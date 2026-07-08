<form method="POST" action="{{ route('category.store') }}">
    @csrf
    <div class="form-group">
        <label for="inputName">Nama Kategori</label>
        <input type="text" class="form-control" name="name" id="inputName">
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Masukkan</button>
    </div>
</form>
