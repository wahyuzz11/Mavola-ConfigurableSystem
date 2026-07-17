<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\DebtHistory;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\SubConfiguration;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Cast\Double;

class JournalEntryController extends Controller
{

    public function index()
    {
        $journals = JournalEntry::orderByDesc('created_at')->paginate(20);
        return view('accounting.index', compact('journals'));
    }


    public static function queryAccountData()
    {
        $balances = Account::query()
            ->select(
                'accounts.id',
                'accounts.code',
                'accounts.name',
                DB::raw('(SUM(journal_entries_details.debit) - SUM(journal_entries_details.credit)) as balance')
            )
            ->join('journal_entries_details', 'journal_entries_details.accounts_id', '=', 'accounts.id')
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name')
            ->orderBy('accounts.code')
            ->get();

        return $balances;
    }


    public function store(Request $request)
    {
        // ── 1. Validasi input ────────────────────────────────────────────────
        $validated = $request->validate([
            'order_date'              => 'required|date',
            'ref_code'                => 'required|string|max:45',
            'description'             => 'nullable|string',
            'accounts'                => 'required|array|min:1',
            'accounts.*.account_code' => 'required|string|exists:accounts,code',
            'accounts.*.debit'        => 'required|numeric|min:0',
            'accounts.*.credit'       => 'required|numeric|min:0',
        ]);

        // ── 2. Pastikan debit = kredit (balance) ─────────────────────────────
        $totalDebit  = collect($validated['accounts'])->sum('debit');
        $totalCredit = collect($validated['accounts'])->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return response()->json([
                'message' => 'Total debit dan kredit harus seimbang.',
                'errors'  => [
                    'balance' => [
                        "Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') .
                            " — Total Kredit: Rp " . number_format($totalCredit, 0, ',', '.')
                    ]
                ]
            ], 422);
        }

        // ── 3. Simpan dalam satu transaksi DB ────────────────────────────────
        $journal = DB::transaction(function () use ($validated) {

            // Buat header jurnal
            $journal = JournalEntry::create([
                'description' => $validated['description'] ?? null,
                'ref_code'    => $validated['ref_code']    ?? null,
                // Kolom created_at bisa diset manual jika ingin pakai order_date
                // 'created_at'  => $validated['order_date'],
            ]);

            // Ambil semua account yang dibutuhkan sekaligus (efisien)
            $codes    = collect($validated['accounts'])->pluck('account_code');
            $accounts = Account::whereIn('code', $codes)->get()->keyBy('code');

            // Buat detail: debit dulu, baru kredit
            $details = collect($validated['accounts'])
                ->sortByDesc('debit') // Debit lebih besar muncul duluan
                ->map(function ($item) use ($journal, $accounts) {
                    $account = $accounts[$item['account_code']];

                    return [
                        'journal_entries_id' => $journal->id,
                        'accounts_id'        => $account->id,
                        'debit'              => $item['debit'],
                        'credit'             => $item['credit'],
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ];
                })->values()->toArray();

            JournalEntryDetail::insert($details);

            return $journal;
        });

        return response()->json([
            'id'      => $journal->id,
            'message' => 'Jurnal berhasil disimpan.',
        ], 201);
    }

    public function queryAccounts(Request $request)
    {
        $search = $request->input('search', '');

        $accounts = Account::where('is_active', 1)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->select('id', 'code', 'name', 'type')
            ->limit(20)
            ->get();

        return response()->json($accounts);
    }

    public function show(JournalEntry $journal)
    {
        $journal->load('journalEntriesDetails.account');
        return view('accounting.show', compact('journal'));
    }


    public function detail(JournalEntry $journal)
    {
        $journal->load('journalEntriesDetails.account');

        return response()->json([
            'description' => $journal->description,
            'ref_code'    => $journal->ref_code,
            'created_at'  => $journal->created_at?->format('d M Y'),
            'details'     => $journal->journalEntriesDetails->map(fn($d) => [
                'debit'   => $d->debit  ?? 0,
                'credit'  => $d->credit ?? 0,
                'account' => [
                    'name' => $d->account->name,
                    'type' => $d->account->type,
                ]
            ])
        ]);
    }

    public function create()
    {
        return view('accounting.create');
    }




    private static function findAccountType(String $code): Account
    {
        $type = Account::where('code', $code)->first();
        return $type;
    }

    public static function storeJournalPurchase(Purchase $purchase): JournalEntry
    {
        try {
            $newJournal = new JournalEntry();
            $deliveryCost = $purchase->delivery_cost
                ? "dan biaya pengiriman sebesar Rp" . number_format($purchase->delivery_cost,'0',
                ',','.') . " "
                : "";
            $description = "Melakukan transaksi pembelian persediaan sebesar Rp" . number_format($purchase->total_price,'0',',','.') .
                " dari supplier " . $deliveryCost . $purchase->supplier->name .
                " dengan metode pembayaran ";

            switch ($purchase->payment_method) {
                case 'P-PAY-01':
                    $description .= "Tunai";
                    break;
                case 'P-PAY-02':
                    $description .= "Transfer bank";
                    break;
                case 'P-PAY-03':
                    $description .= "Hutang";
                    break;
            }
            $newJournal->ref_code = $purchase->invoice_number;
            $newJournal->description = $description;
            $newJournal->purchases_id = $purchase->id;
            $newJournal->save();
            return $newJournal;
        } catch (Exception $e) {
            throw new Exception("Error in " . __FUNCTION__ . " : " . $e->getMessage());
        }
    }

    public static function storeJournalDebt(DebtHistory $debt): JournalEntry
    {
        try {
            $newJournal = new JournalEntry();
            $description = "Melakukan pembayaran hutang transaksi pembelian kepada " . $debt->supplier->company_name . "sebesar Rp" . number_format($debt->debt_nominal,0,',','.') . " 
            dengan metode pembayaran " . $debt->payment_method;
            $newJournal->ref_code = $debt->purchase->invoice_number . "-Debt_Paid";
            $newJournal->description = $description;
            $newJournal->purchases_id = $debt->purchase_id;
            $newJournal->save();
            return $newJournal;
        } catch (Exception $e) {
            throw new Exception("Error in " . __FUNCTION__ . " : " . $e->getMessage());
        }
    }


    public static function storeJournalSale($sale): JournalEntry
    {
        try {
            $newJournal = new JournalEntry();

            $payment_method = match ($sale->payment_method) {
                'S-PAY-01' => "Tunai",
                'S-PAY-02' => "Transfer bank",
                'S-PAY-03' => "Qris",
                default => "",
            };

            $delivery_method = match ($sale->delivery_method) {
                'DEL-01' => ", pengiriman diambil sendiri",
                'DEL-02' => ", pengiriman diantar",
                default => "",
            };

            $formattedTotal = "Rp" . number_format($sale->total_price, 0, ',', '.');

            // Kumpulkan info diskon yang benar-benar dipakai (nilai > 0 saja)
            $discountParts = [];

            if ($sale->total_discount > 0) {
                $discountParts[] = "diskon produk sebesar Rp" . number_format($sale->total_discount, 0, ',', '.');
            }

            if ($sale->global_discount > 0) {
                $discountParts[] = "diskon global sebesar Rp" . number_format($sale->global_discount, 0, ',', '.');
            }

            if ($sale->discount_cashback > 0) {
                $discountParts[] = "cashback sebesar Rp" . number_format($sale->discount_cashback, 0, ',', '.');
            }

            $discount_method = !empty($discountParts)
                ? " dengan " . implode(", ", $discountParts)
                : "";

            $subConfiguration = SubConfiguration::where('configurations_id', 1)
                ->where('status', 1)
                ->first();


            $description = "Melakukan transaksi penjualan sebesar " . $formattedTotal . " kepada " .
                $sale->customer->name . " dengan metode pembayaran " . $payment_method . $delivery_method . $discount_method. ", perhitungan HPP dilakukan dengan metode ". $subConfiguration->name;



            $newJournal->ref_code = $sale->invoice_number;
            $newJournal->description = $description;
            $newJournal->sales_id = $sale->id;
            $newJournal->save();

            return $newJournal;
        } catch (Exception $e) {
            throw new Exception("Error in " . __FUNCTION__ . " : ", $e->getMessage());
        }
    }


    public static function checkPurchaseAccount(Purchase $purchase): array
    {

        $baseAccounts =  match ($purchase->payment_method) {
            'P-PAY-01' => [
                ['code' => "1102", 'position' => 'debit'],
                ['code' => "1101", 'position' => 'credit'],
            ],
            'P-PAY-02' => [
                ['code' => '1102', 'position' => 'debit'],
                ['code' => '1103', 'position' => 'credit'],

            ],
            'P-PAY-03' => [
                ['code' => '1102', 'position' => 'debit'],
                ['code' => '2101', 'position' => 'credit'],
            ],
            default => throw new \Exception("Unrecognizable payment, havent finished the code yet: {$purchase->payment_method}"),
        };

        $deliveryAccounts = $purchase->delivery_cost ? [
            ['code' => '6101', 'position' => 'debit'],
            ['code' => $baseAccounts[1]['code'], 'position' => 'credit'],
        ] : [];

        return [
            'base_accounts' => $baseAccounts,
            'delivery_accounts' => $deliveryAccounts
        ];
    }

    public static function checkDebtAccount(DebtHistory $debt): array
    {
        $baseAccounts = match ($debt->payment_method) {
            'Tunai' => [
                ['code' => '2101', 'position' => 'debit'],
                ['code' => '1101', 'position' => 'credit']
            ],
            'Transfer' => [
                ['code' => '2101', 'position' => 'debit'],
                ['code' => '1102', 'position' => 'credit']
            ],
            '' => throw new \Exception("Kosong"),
        };
        return $baseAccounts;
    }


    public static function checkSaleAccount(sale $sale): array
    {
        $baseAccounts =  match ($sale->payment_method) {
            'S-PAY-01' => [
                ['code' => "1101", 'position' => 'debit'],
                ['code' => "4101", 'position' => 'credit'],
            ],
            'S-PAY-02' => [
                ['code' => '1103', 'position' => 'debit'],
                ['code' => '4101', 'position' => 'credit'],

            ],
            'S-PAY-03' => [
                ['code' => '1103', 'position' => 'debit'],
                ['code' => '4101', 'position' => 'credit'],
            ],
            default => throw new \Exception("Unrecognizable payment, havent finished the code yet: {$sale->payment_method}"),
        };

        $deliveryAccounts = $sale->delivery_cost ? [
            ['code' => '6102', 'position' => 'debit'],
            ['code' => $baseAccounts[0]['code'], 'position' => 'credit'],
        ] : [];


        $discountAccounts = $sale->global_discount || $sale->total_discount || $sale->discount_cashback ? [
            ['code' => '4102', 'position' => 'debit'],
        ] : [];


        $cogsAccounts = [
            ['code' => '5101', 'position' => 'debit'],
            ['code' => '1102', 'position' => 'credit'],
        ];


        return [
            'base_accounts' => $baseAccounts,
            'cogs_accounts' => $cogsAccounts,
            'discount_accounts' => $discountAccounts,
            'delivery_accounts' => $deliveryAccounts
        ];
    }


    public static function storeJournalEntry(string $code, string $position, int $value, string $entryId): void
    {
        try {
            $newEntryDetail = new JournalEntryDetail();
            $account = self::findAccountType($code);
            if ($position == 'debit') {
                $newEntryDetail->debit = $value;
            } else {
                $newEntryDetail->credit = $value;
            }
            $newEntryDetail->journal_entries_id = $entryId;
            $newEntryDetail->accounts_id = $account->id;
            $newEntryDetail->save();
        } catch (Exception $e) {
            throw new Exception("Error in " . __FUNCTION__ . " : " . $e->getMessage());
        }
    }
}
