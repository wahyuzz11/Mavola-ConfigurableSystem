<form method="POST" action="{{ route('warehouse.store') }}">
    @csrf
    <div class="form-group">
        <label for="inputDescription">Warehouse Name</label>
        <input type="text" class="form-control" name="name" id="inputName">
        <label for="inputDescription">Warehouse Address</label>
        <input type="text" class="form-control" name="address" id="inputAddress">
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</form>
