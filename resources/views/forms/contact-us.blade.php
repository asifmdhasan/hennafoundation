@extends('layouts.app')

@section('title', 'Contact Us'.($settings->get('site_name') ? ' | '.$settings->get('site_name') : ''))
@section('canonical_url', url('/contact-us/'))

@section('content')
    <section class="section-pad form-page" aria-labelledby="form-title">
        <div class="container form-page__container">
            <header class="section-heading section-heading--centered">
                <h1 id="form-title">Contact Us</h1>
            </header>

            @if (session('form_success'))
                <p class="form-success" role="status">{{ session('form_success') }}</p>
            @endif

            <form class="public-form" method="post" action="{{ route('contact-us.store') }}" novalidate>
                @csrf
                @if ($errors->any())
                    <div class="form-errors" role="alert" aria-labelledby="form-error-title">
                        <h2 id="form-error-title">Please correct the highlighted fields.</h2>
                    </div>
                @endif

                <div class="public-form__field">
                    <label for="name">Name <span aria-hidden="true">*</span></label>
                    <input id="name" name="name" type="text" autocomplete="name" maxlength="255" required value="{{ old('name') }}" @if ($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif>
                    @error('name')<p class="form-error" id="name-error">{{ $message }}</p>@enderror
                </div>
                <div class="public-form__field">
                    <label for="email">Email <span aria-hidden="true">*</span></label>
                    <input id="email" name="email" type="email" autocomplete="email" maxlength="255" required value="{{ old('email') }}" @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
                    @error('email')<p class="form-error" id="email-error">{{ $message }}</p>@enderror
                </div>
                <div class="public-form__field">
                    <label for="subject">Subject <span aria-hidden="true">*</span></label>
                    <input id="subject" name="subject" type="text" maxlength="255" required value="{{ old('subject') }}" @if ($errors->has('subject')) aria-invalid="true" aria-describedby="subject-error" @endif>
                    @error('subject')<p class="form-error" id="subject-error">{{ $message }}</p>@enderror
                </div>
                <div class="public-form__field">
                    <label for="message">Message <span aria-hidden="true">*</span></label>
                    <textarea id="message" name="message" rows="7" maxlength="10000" required @if ($errors->has('message')) aria-invalid="true" aria-describedby="message-error" @endif>{{ old('message') }}</textarea>
                    @error('message')<p class="form-error" id="message-error">{{ $message }}</p>@enderror
                </div>
                <button class="button button--primary" type="submit">Send Message</button>
            </form>
        </div>
    </section>
@endsection
