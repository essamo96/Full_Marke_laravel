<x-guest-layout>
    <!--begin::Authentication - Sign-up -->
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <!--begin::Aside-->
        <div class="d-flex flex-lg-row-fluid">
            <!--begin::Content-->
            <div class="d-flex flex-column flex-center pb-0 pb-lg-10 p-10 w-100">
                <img class="theme-light-show mx-auto mw-100 w-150px w-lg-300px mb-10 mb-lg-20"
                    src="{{ asset('admin/assets/media/auth/agency.png') }}" alt="" />
                <img class="theme-dark-show mx-auto mw-100 w-150px w-lg-300px mb-10 mb-lg-20"
                    src="{{ asset('admin/assets/media/auth/agency-dark.png') }}" alt="" />

                <h1 class="text-gray-800 fs-2qx fw-bold text-center mb-7">Fast, Efficient and Productive</h1>

                <div class="text-gray-600 fs-base text-center fw-semibold">
                    In this kind of post,
                    <a href="#" class="opacity-75-hover text-primary me-1">the blogger</a>
                    introduces a person they’ve interviewed
                    <br />and provides some background information about
                    <a href="#" class="opacity-75-hover text-primary me-1">the interviewee</a> and their
                    <br />work following this is a transcript of the interview.
                </div>
            </div>
            <!--end::Content-->
        </div>
        <!--end::Aside-->

        <!--begin::Body-->
        <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12">
            <div class="bg-body d-flex flex-column flex-center rounded-4 w-md-600px p-10">
                <div class="d-flex flex-center flex-column align-items-stretch h-lg-100 w-md-400px">

                    <div class="d-flex flex-center flex-column flex-column-fluid pb-15 pb-lg-20">
                        <!--begin::Form-->
                        <form class="form w-100" method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="text-center mb-11">
                                <h1 class="text-dark fw-bolder mb-3">Sign Up</h1>
                                <div class="text-gray-500 fw-semibold fs-6">Your Social Campaigns</div>
                            </div>

                            <!-- Login with Google / Apple -->
                            <div class="row g-3 mb-9">
                                <div class="col-md-6">
                                    <a href="#"
                                        class="btn btn-flex btn-outline btn-text-gray-700 btn-active-color-primary bg-state-light flex-center text-nowrap w-100">
                                        <img alt="Logo"
                                            src="{{ asset('admin/assets/media/svg/brand-logos/google-icon.svg') }}"
                                            class="h-15px me-3" />Sign in with Google
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="#"
                                        class="btn btn-flex btn-outline btn-text-gray-700 btn-active-color-primary bg-state-light flex-center text-nowrap w-100">
                                        <img alt="Logo"
                                            src="{{ asset('admin/assets/media/svg/brand-logos/apple-black.svg') }}"
                                            class="theme-light-show h-15px me-3" />
                                        <img alt="Logo"
                                            src="{{ asset('admin/assets/media/svg/brand-logos/apple-black-dark.svg') }}"
                                            class="theme-dark-show h-15px me-3" />
                                        Sign in with Apple
                                    </a>
                                </div>
                            </div>

                            <div class="separator separator-content my-14">
                                <span class="w-125px text-gray-500 fw-semibold fs-7">Or with email</span>
                            </div>

                            <!-- Email -->
                            <div class="fv-row mb-8">
                                <input type="email" name="email" class="form-control bg-transparent"
                                    placeholder="Email" required autofocus>
                            </div>

                            <!-- Password -->
                            <div class="fv-row mb-8" data-kt-password-meter="true">
                                <div class="mb-1">
                                    <div class="position-relative mb-3">
                                        <input class="form-control bg-transparent" type="password" name="password"
                                            placeholder="Password" required>
                                        <span
                                            class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2">
                                            <i class="ki-duotone ki-eye-slash fs-2"></i>
                                            <i class="ki-duotone ki-eye fs-2 d-none"></i>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2">
                                        </div>
                                        <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2">
                                        </div>
                                        <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2">
                                        </div>
                                        <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px"></div>
                                    </div>
                                </div>
                                <div class="text-muted">Use 8 or more characters with a mix of letters, numbers &
                                    symbols.</div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="fv-row mb-8">
                                <input type="password" name="password_confirmation" class="form-control bg-transparent"
                                    placeholder="Repeat Password" required>
                            </div>

                            <!-- Accept Terms -->
                            <div class="fv-row mb-8">
                                <label class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="terms" required>
                                    <span class="form-check-label fw-semibold text-gray-700 fs-base ms-1">I Accept the
                                        <a href="#" class="ms-1 link-primary">Terms</a></span>
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid mb-10">
                                <button type="submit" class="btn btn-primary">
                                    <span class="indicator-label">Sign up</span>
                                </button>
                            </div>

                            <div class="text-gray-500 text-center fw-semibold fs-6">Already have an Account?
                                <a href="{{ route('login') }}" class="link-primary fw-semibold">Sign in</a>
                            </div>
                        </form>
                        <!--end::Form-->
                    </div>

                    <div class="w-lg-500px d-flex flex-stack">
                        <div class="me-10">
                            <!-- Language dropdown or static info -->
                            <span class="text-muted">English</span>
                        </div>
                        <div class="d-flex fw-semibold text-primary fs-base gap-5">
                            <a href="#">Terms</a>
                            <a href="#">Plans</a>
                            <a href="#">Contact Us</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!--end::Body-->
    </div>
    <!--end::Authentication - Sign-up -->
    @section('js')
        <script src="{{ asset('admin/assets/js/custom/authentication/sign-up/general.js') }}"></script>
    @endsection
</x-guest-layout>
