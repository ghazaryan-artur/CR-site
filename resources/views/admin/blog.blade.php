{{ 'Welcome to Admin Blog page, Content will be soon!' }}
<form method="POST" action="/admin/blog/create" enctype="multipart/form-data">
    {{ csrf_field() }}
        <input type="text" name="pageTitle">
<input type="submit">
</form>
{{ $errors->first('blogImage') ?? '' }}
