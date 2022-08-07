@extends($activeTemplate.'layouts.frontend')
@section('content')
    @include($activeTemplate.'partials.user-breadcrumb')
<section class="pt-150">
    <div class="signup-wrapper pt-150 pb-150">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-xl-8 col-lg-10">
                    <div class="signup-form-area">
                        <h3 class="title text-capitalize text-shadow mb-30">{{__($page_title)}}</h3>

                        <form class="signup-form" action="{{ route('user.register')}}" method="post"
                              onsubmit="return submitUserForm();">
                            @csrf

                            <div class="row">
                                @if(session()->get('reference') != null)
                                    <div class="col-lg-12 form-group custom-form-field">
                                        <i class="fa fa-user"></i>
                                        <input type="text" name="referBy" id="referenceBy"
                                               placeholder="{{trans('Reference By') }}"
                                               value="{{session()->get('reference')}}" readonly>
                                    </div>
                                @endif


                                <div class="col-lg-6 form-group ">
                                    <label for="firstname">{{ trans('First Name') }}</label>
                                    <input type="text" name="firstname" id="firstname"
                                           placeholder="{{ trans('First Name') }}" value="{{ old('firstname') }}" required>
                                </div>

                                <div class="col-lg-6 form-group">
                                    <label for="lastname">{{ trans('Last Name') }}</label>
                                    <input type="text" name="lastname" id="lastname"
                                           placeholder="{{ trans('Last Name') }}"
                                           value="{{ old('lastname') }}" required>
                                </div>


                                <div class="col-lg-6 form-group">
                                    <label>{{ trans('Username') }}</label>
                                    <input type="text" name="username" id="signup_username" value="{{old('username')}}"
                                           placeholder="{{ trans('Username')}}" autocomplete="off" required>
                                </div>

                                <div class="col-lg-6 form-group">
                                    <label>{{ trans('Email Address') }}</label>
                                    <input type="email" name="email" id="signup_email"
                                           placeholder="{{trans('Email Address') }}" value="{{old('email')}}" required>
                                </div>

                                <div class="col-lg-6 form-group">
                                    <label>{{ __('Country') }}</label>
                                    <select name="country" id="country" class="form-control country-select">
                                        @foreach($countries as $key => $country)
                                            <option data-mobile_code="{{ $country->dial_code }}" value="{{ $country->country }}" data-code="{{ $key }}">{{ __($country->country) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-6 form-group">
                                    <label>{{ trans('Mobile') }}</label>
                                    <div class="input-group ">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text mobile-code">
                                                    
                                            </span>
                                            <input type="hidden" name="mobile_code">
                                        </div>
                                        <input type="text" name="mobile" class="form-control" placeholder="@lang('Your Phone Number')" required>
                                    </div>
                                </div>




                                <div class="col-lg-6 form-group">
                                    <label>{{ trans('Password') }}</label>
                                    <input type="password" name="password" id="signup_pass" placeholder="@lang('Password')" required>
                                </div>

                                <div class="col-lg-6 form-group">
                                    <label>{{ trans('Confirm Password') }}</label>
                                    <input type="password" name="password_confirmation" id="signup_re-pass"
                                           placeholder="@lang('Confirm Password')" required>
                                </div>

                                <div class="col-lg-12 form-group">
                                    @php echo recaptcha() @endphp
                                </div>
                                @include($activeTemplate.'partials.custom-captcha')
                                @php
                                      $links = getContent('links.element','','',1);
                                  @endphp
                                <div class="col-lg-12 form-group">
                                    <input type="checkbox" name="terms" required> <span class="f-size-14 ml-2">@lang('I agree with') @foreach($links as $link) 
                                    <a class="base--color" href="{{ route('linkDetails',[slug($link->data_values->title),$link->id]) }}"> @lang($link->data_values->title)</a>
                                    @if(!$loop->last) , @endif @endforeach</span>
                                </div>
                                <div class="col-lg-12 form-group">
                                    <button type="submit" class="btn btn-danger btn-small w-100 btn-primary">{{trans('Sign Up')}}</button>
                                </div>
                                <p>{{trans('Already have an account?')}}
                                    <a href="{{route('user.login')}}" class="pl-2">{{trans('Sign In')}}</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('style')
    <link href="{{asset($activeTemplateTrue).'/css/intlTelInput.css'}}" rel="stylesheet">
    <style>
        .iti {
            width: 100%;
        }

        .iti__country-list {
            box-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);
            background-color: #292a38;
        }
        input[type="checkbox"] {
            width: auto;
            padding: 0;
            height: auto;
            min-height: auto;
            box-shadow: none;
        }

        .input-group-text {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-align: center;
            align-items: center;
            padding: .375rem .75rem;
            margin-bottom: 0;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            text-align: center;
            white-space: nowrap;
            background-color: transparent;
            border: 1px solid #ced4da3b;
            border-radius: .25rem;
            color: #fff;
        }

    </style>
@endpush

@push('script')
    <script>
        "use strict";
            @if($mobile_code)
            $(`option[data-code={{ $mobile_code }}]`).attr('selected','');
            @endif

            $('select[name=country]').change(function(){
                $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
                $('.mobile-code').text('+'+$('select[name=country] :selected').data('mobile_code'));
            });
            $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
            $('.mobile-code').text('+'+$('select[name=country] :selected').data('mobile_code'));
        function submitUserForm() {
            var response = grecaptcha.getResponse();
            if (response.length == 0) {
                document.getElementById('g-recaptcha-error').innerHTML = '<span style="color:red;">@lang("Captcha field is required.")</span>';
                return false;
            }
            return true;
        }

        function verifyCaptcha() {
            document.getElementById('g-recaptcha-error').innerHTML = '';
        }
    </script>
@endpush

