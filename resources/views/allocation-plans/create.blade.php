@extends('layouts.master')

@section('content')
    <div class="container-fluid py-4 px-4">
        <div class="card border-0" style="border-radius: 16px; box-shadow: 0 4px 28px rgba(16, 92, 58, 0.11); overflow: hidden;">
            <div class="card-header bg-success text-white py-3 px-4">
                <h5 class="mb-0 fw-bold">ပြဌာန်းစာအုပ်ခွဲတမ်းတွက်ချက်မှုဖန်တီးရန်</h5>
            </div>
            <div class="card-body" style="padding: 24px 28px;">
                <form method="POST" action="{{ route('allocation-plans.store') }}">
                    @csrf
                    @include('allocation-plans._form')
                </form>
            </div>
        </div>
    </div>
@endsection
