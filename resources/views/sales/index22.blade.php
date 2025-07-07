@extends('layouts.index')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            Sales reports
        </h3>
        <div class="ms-md-auto py-2 py-md-0">
            <a href="{{ route('sales.create') }}" class="btn btn-primary btn-round">Create Sales Order</a>
        </div>
    </div>

    <div class="section">
        <div class="section-header pending-header">
            Pending Purchase Orders
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Total Amount</th>
                    <th>Payment Terms</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PO001056</td>
                    <td>28 Jun 2025</td>
                    <td>ABC Supplies Ltd</td>
                    <td class="price">Rp 25,750,000.00</td>
                    <td>Net 30</td>
                    <td><span class="badge badge-warning">Pending Approval</span></td>
                    <td><a href="#" class="btn btn-primary btn-sm">Approve Order</a></td>
                </tr>
                <tr>
                    <td>PO001055</td>
                    <td>27 Jun 2025</td>
                    <td>Tech Solutions Inc</td>
                    <td class="price">Rp 8,900,000.00</td>
                    <td>Net 15</td>
                    <td><span class="badge badge-info">Approved</span></td>
                    <td><a href="#" class="btn btn-success btn-sm">Send to Supplier</a></td>
                </tr>
                <tr>
                    <td>PO001054</td>
                    <td>26 Jun 2025</td>
                    <td>Office Mart</td>
                    <td class="price">Rp 3,250,000.00</td>
                    <td>COD</td>
                    <td><span class="badge badge-warning">Pending Approval</span></td>
                    <td><a href="#" class="btn btn-primary btn-sm">Approve Order</a></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section mt-4">
        <div class="section-header completed-header">
            Completed Purchase Orders
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Recipient</th>
                    <th>Delivery Address</th>
                    <th>Total Amount</th>
                    <th>Delivery Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PO001053</td>
                    <td>25 Jun 2025</td>
                    <td>Industrial Parts Co</td>
                    <td>Warehouse Team</td>
                    <td>Main Warehouse</td>
                    <td class="price">Rp 15,400,000.00</td>
                    <td>25 Jun 2025</td>
                    <td><a href="#" class="btn btn-info btn-sm">View Details</a></td>
                </tr>
                <tr>
                    <td>PO001052</td>
                    <td>24 Jun 2025</td>
                    <td>Global Electronics</td>
                    <td>IT Department</td>
                    <td>Head Office</td>
                    <td class="price">Rp 45,200,000.00</td>
                    <td>24 Jun 2025</td>
                    <td><a href="#" class="btn btn-info btn-sm">View Details</a></td>
                </tr>
                <tr>
                    <td>PO001051</td>
                    <td>23 Jun 2025</td>
                    <td>Packaging Solutions</td>
                    <td>Production Team</td>
                    <td>Factory Floor</td>
                    <td class="price">Rp 7,850,000.00</td>
                    <td>23 Jun 2025</td>
                    <td><a href="#" class="btn btn-info btn-sm">View Details</a></td>
                </tr>
                <tr>
                    <td>PO001050</td>
                    <td>22 Jun 2025</td>
                    <td>Raw Materials Corp</td>
                    <td>Production Team</td>
                    <td>Storage Area B</td>
                    <td class="price">Rp 32,100,000.00</td>
                    <td>22 Jun 2025</td>
                    <td><a href="#" class="btn btn-info btn-sm">View Details</a></td>
                </tr>
                <tr>
                    <td>PO001049</td>
                    <td>21 Jun 2025</td>
                    <td>Office Supplies Plus</td>
                    <td>Admin Team</td>
                    <td>Office Building</td>
                    <td class="price">Rp 2,750,000.00</td>
                    <td>21 Jun 2025</td>
                    <td><a href="#" class="btn btn-info btn-sm">View Details</a></td>
                </tr>
                <tr>
                    <td>PO001048</td>
                    <td>20 Jun 2025</td>
                    <td>Equipment Rental Co</td>
                    <td>Maintenance Team</td>
                    <td>Service Bay</td>
                    <td class="price">Rp 18,900,000.00</td>
                    <td>20 Jun 2025</td>
                    <td><a href="#" class="btn btn-info btn-sm">View Details</a></td>
                </tr>
                <tr>
                    <td>PO001047</td>
                    <td>19 Jun 2025</td>
                    <td>Chemical Supplies Ltd</td>
                    <td>Safety Team</td>
                    <td>Chemical Storage</td>
                    <td class="price">Rp 12,300,000.00</td>
                    <td>19 Jun 2025</td>
                    <td><a href="#" class="btn btn-info btn-sm">View Details</a></td>
                </tr>
                <tr>
                    <td>PO001046</td>
                    <td>18 Jun 2025</td>
                    <td>Transport Solutions</td>
                    <td>Logistics Team</td>
                    <td>Loading Dock</td>
                    <td class="price">Rp 6,500,000.00</td>
                    <td>18 Jun 2025</td>
                    <td><a href="#" class="btn btn-info btn-sm">View Details</a></td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
