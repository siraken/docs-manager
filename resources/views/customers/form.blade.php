@extends('layouts/default')
@section('page')

<form method="post" action="" autocomplete="off" id="MainForm">
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">Back</a>
            <button class="btn btn-secondary" type="submit">Save</button>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            @csrf

            <div class="mb-3">
                <label class="form-label">Name<span class="badge bg-danger ms-1">Required</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $customer['name']) }}" required>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_company" role="switch" id="is-company-switch" checked="{{ $customer->is_company === 1 ? "true" : "false" }}">
                    <label class="form-check-label" for="is-company-switch">This is a company</label>
                  </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email<span class="badge bg-danger ms-1">Required</span></label>
                <input type="text" name="email" class="form-control" value="{{ old('email', $customer['email']) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone<span class="badge bg-danger ms-1">Required</span></label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer['phone']) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Post code<span class="badge bg-danger ms-1">Required</span></label>
                <input type="text" name="post_code" class="form-control" value="{{ old('post_code', $customer['post_code']) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Address<span class="badge bg-danger ms-1">Required</span></label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $customer['address']) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">City<span class="badge bg-danger ms-1">Required</span></label>
                <input type="text" name="city" class="form-control" value="{{ old('city', $customer['city']) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">State<span class="badge bg-danger ms-1">Required</span></label>
                <input type="text" name="state" class="form-control" value="{{ old('state', $customer['state']) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Country<span class="badge bg-danger ms-1">Required</span></label>
                <input type="text" name="country" class="form-control" value="{{ old('country', $customer['country']) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Note</label>
                <textarea name="note" class="form-control">{{ old('note', $customer['note']) }}</textarea>
            </div>

        </div>
    </div>
</form>

@endsection
