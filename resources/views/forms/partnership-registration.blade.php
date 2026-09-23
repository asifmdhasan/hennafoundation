@extends('layouts.app')

@section('title', 'Partnership Registration'.($settings->get('site_name') ? ' | '.$settings->get('site_name') : ''))
@section('canonical_url', url('/partnership-registration/'))

@section('content')
    <section class="section-pad form-page" aria-labelledby="form-title">
        <div class="container form-page__container">
            <header class="section-heading section-heading--centered">
                <h1 id="form-title">Partnership Registration</h1>
            </header>

            @if (session('form_success'))
                <p class="form-success" role="status">{{ session('form_success') }}</p>
            @endif

            <form class="public-form" method="post" action="{{ route('partnership-registration.store') }}">
                @csrf
                @if ($errors->any())
                    <div class="form-errors" role="alert" aria-labelledby="form-error-title">
                        <h2 id="form-error-title">Please correct the highlighted fields.</h2>
                    </div>
                @endif

                <div class="public-form__grid">
                    <div class="public-form__field">
                        <label for="full_name">Full Name <span aria-hidden="true">*</span></label>
                        <input id="full_name" name="full_name" type="text" autocomplete="name" maxlength="255" required value="{{ old('full_name') }}" @if ($errors->has('full_name')) aria-invalid="true" aria-describedby="full_name-error" @endif>
                        @error('full_name')<p class="form-error" id="full_name-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="public-form__field">
                        <label for="organisation_name">Organisation Name <span aria-hidden="true">*</span></label>
                        <input id="organisation_name" name="organisation_name" type="text" autocomplete="organization" maxlength="255" required value="{{ old('organisation_name') }}" @if ($errors->has('organisation_name')) aria-invalid="true" aria-describedby="organisation_name-error" @endif>
                        @error('organisation_name')<p class="form-error" id="organisation_name-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="public-form__field">
                        <label for="phone">Phone <span aria-hidden="true">*</span></label>
                        <input id="phone" name="phone" type="tel" autocomplete="tel" maxlength="40" required value="{{ old('phone') }}" @if ($errors->has('phone')) aria-invalid="true" aria-describedby="phone-error" @endif>
                        @error('phone')<p class="form-error" id="phone-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="public-form__field">
                        <label for="email">Email <span aria-hidden="true">*</span></label>
                        <input id="email" name="email" type="email" autocomplete="email" maxlength="255" required value="{{ old('email') }}" @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
                        @error('email')<p class="form-error" id="email-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="public-form__field">
                        <label for="home_telephone">Home Telephone <span aria-hidden="true">*</span></label>
                        <input id="home_telephone" name="home_telephone" type="tel" autocomplete="home tel" maxlength="40" required value="{{ old('home_telephone') }}" @if ($errors->has('home_telephone')) aria-invalid="true" aria-describedby="home_telephone-error" @endif>
                        @error('home_telephone')<p class="form-error" id="home_telephone-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="public-form__field">
                        <label for="address_1">Address 1 <span aria-hidden="true">*</span></label>
                        <input id="address_1" name="address_1" type="text" autocomplete="address-line1" maxlength="255" required value="{{ old('address_1') }}" @if ($errors->has('address_1')) aria-invalid="true" aria-describedby="address_1-error" @endif>
                        @error('address_1')<p class="form-error" id="address_1-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="public-form__field">
                        <label for="address_2">Address 2</label>
                        <input id="address_2" name="address_2" type="text" autocomplete="address-line2" maxlength="255" value="{{ old('address_2') }}" @if ($errors->has('address_2')) aria-invalid="true" aria-describedby="address_2-error" @endif>
                        @error('address_2')<p class="form-error" id="address_2-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="public-form__field">
                        <label for="city">City <span aria-hidden="true">*</span></label>
                        <input id="city" name="city" type="text" autocomplete="address-level2" maxlength="120" required value="{{ old('city') }}" @if ($errors->has('city')) aria-invalid="true" aria-describedby="city-error" @endif>
                        @error('city')<p class="form-error" id="city-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="public-form__field">
                        <label for="postal_code">ZIP / Postal Code <span aria-hidden="true">*</span></label>
                        <input id="postal_code" name="postal_code" type="text" autocomplete="postal-code" maxlength="32" required value="{{ old('postal_code') }}" @if ($errors->has('postal_code')) aria-invalid="true" aria-describedby="postal_code-error" @endif>
                        @error('postal_code')<p class="form-error" id="postal_code-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <button class="button button--primary" type="submit">Submit Registration</button>
            </form>
        </div>
    </section>
@endsection
