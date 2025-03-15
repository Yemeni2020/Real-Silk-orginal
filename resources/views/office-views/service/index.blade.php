@extends('layouts.back-end.app-seller')

@section('title')

@section('content')

<div class="card">
    <div class="card-header">
        <h1>{{translate("Select_Your_Service")}}</h1>
    </div>
    <div class="card-body">
        <form action="{{ route('office.service.store') }}" method="POST">
            @csrf
            <div class="row">
                @foreach($products as $product)
                    <div class="col-4">
                        <input type="checkbox" id="prod-{{ $product->id }}" name="products[]"
                            value="{{ $product->id }}" 
                            {{ in_array($product->id, $registeredServices) ? 'checked' : '' }}>
                        <label for="prod-{{ $product->id }}">{{ $product->name }}</label>
                    </div>
                @endforeach
            </div>
            <button type="submit" class="btn btn-primary">{{ translate('Save') }}</button>
        </form>



    </div>
</div>


@endsection
