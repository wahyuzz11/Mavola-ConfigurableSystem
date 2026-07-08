<form method="POST" action="{{ route('suppliers.store') }}">
    @csrf
    <div class="form-group">
        <label for="companyName">Nama Perusahaan</label>
        <input type="text" class="form-control" name="company_name" id="companyName">
        <label for="ownerName">Nama Pemilik</label>
        <input type="text" class="form-control" name="owner_name" id="ownerName">
        <label for="phoneNumber">No Hp</label>
        <input type="text" class="form-control" name="phone_number" id="phoneNumber">
        <label for="address">Alamat</label>
        <input type="text" class="form-control" name="address" id="address">
        <label for="email">Email</label>
        <input type="email" class="form-control" name="email" id="email">
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</form>
