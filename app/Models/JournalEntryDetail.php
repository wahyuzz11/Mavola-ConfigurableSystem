<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryDetail extends Model
{
    use HasFactory;

    protected $table = 'journal_entries_details';

    public function journalEntry():BelongsTo{
        return $this->belongsTo(JournalEntry::class,'journal_entries_id');

    }

    public function account():BelongsTo{
        return $this->belongsTo(Account::class,'accounts_id');
    }

    
}
