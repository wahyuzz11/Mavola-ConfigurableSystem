@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            Customer List
        </h3>

        <div class="ms-md-auto py-2 py-md-0">
            <button type="button" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#addModal">Add Customer</button>
        </div>
    </div>

    <div class="card">
        <table id="basic-datatables" class="display table table-striped table-hover dataTable" role="grid"
            aria-describedby="basic-datatables_info">
            <thead>
                <tr role="row">
                    <th>
                        Customer Name
                    </th>
                    <th>
                        Contact1
                    </th>
                    <th>
                        address
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $customer)
                <tr>
                    <td>
                        {{
                            $customer->name
                        }}
                    </td>
                    <td>
                        {{
                            $customer->contact1
                        }}
                    </td>
                    <td>
                        {{
                            $customer->address
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
                    <h5 class="modal-title" id="addModalLabel">Add Customer</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>

                    </button>
                </div>
                <div class="modal-body">
                    @include('sales.addCustomer')
                </div>

            </div>
        </div>
    </div>
    <!-- End of Add Modal -->
@endsection
