@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            Suppliers
        </h3>

        <div class="ms-md-auto py-2 py-md-0">
            <button type="button" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#addModal">Suppliers</button>
        </div>
    </div>

    <div class="card">
        <table id="basic-datatables" class="display table table-striped table-hover dataTable" role="grid"
            aria-describedby="basic-datatables_info">
            <thead>
                <tr role="row">
                    <th>
                        Company name
                    </th>
                    <th>
                        phone number
                    </th>
                    <th>
                        owner name
                    </th>
                    <th>
                        address
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($suppliers as $supplier)
                <tr>
                    <td>
                        {{
                            $supplier->company_name
                        }}
                    </td>
                    <td>
                        {{
                            $supplier->phone_number
                        }}
                    </td>
                    <td>
                        {{
                            $supplier->owner_name
                        }}
                    </td>

                    <td>
                        {{

                            $supplier->address
                        }}
                    </td>
                </tr>


                @endforeach 
            </tbody>
        </table>

    </div>


    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Add Suppliers</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>

                    </button>
                </div>
                <div class="modal-body">
                   @include('purchases.addSuppliers')
                </div>

            </div>
        </div>
    </div>
    <!-- End of Add Modal -->
@endsection
