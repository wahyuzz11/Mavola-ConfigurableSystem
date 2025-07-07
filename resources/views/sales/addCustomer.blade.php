<form method="POST" action="{{ route('customers.store') }}">
    @csrf
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" class="form-control" name="name" id="name">
        <label for="contact1">Phone Number 1</label>
        <input type="text" class="form-control" name="contact1" id="contact1">
        <label for="contact2">Phone Number 2</label>
        <input type="text" class="form-control" name="contact2" id="contact2">
        <label for="address">Address</label>
        <input type="text" class="form-control" name="address" id="address">
        <label for="email">Email</label>
        <input type="email" class="form-control" name="email" id="email">
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</form>
