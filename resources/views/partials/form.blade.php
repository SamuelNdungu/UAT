<form action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($policy->id))
        @method('PUT')
    @endif

    <!-- Include all form fields here (as designed earlier) -->
    @include('policies.form-fields', ['policy' => $policy])

    <div class="form-group">
        <button type="submit" class="btn btn-success">Save Policy</button>
    </div>
</form>
