<div class="card-title">

    <div class="mb-0 mx-2">
        <label class="form-label">{{ \App\Helpers\translate('name') }}</label>
        <input type="text" id="generalSearch" value="{{ old('name') }}"
            class="form-control form-control-solid  ps-13 generalSearch" placeholder="{{ \App\Helpers\translate('searsh') }}" />
    </div>

    <div class="mb-0 mx-2">
        <label class="form-label">{{ \App\Helpers\translate('from_close_date') }}</label>
        <input class="form-control filter_date" value="{{ date('Y-m-01') }}" id="from_date" />
    </div>

    <div class="mb-0 mx-2">
        <label class="form-label">{{ \App\Helpers\translate('to_close_date') }}</label>
        <input class="form-control filter_date" value="{{ date('Y-m-t') }}" id="to_date" />
    </div>
    
    @if (Route::currentRouteName() == 'holidays.view' || Route::currentRouteName() == 'holiday_requests.view')
        {{-- || Route::currentRouteName() == 'admin.edit' --}}
        <div class="mb-0 mx-2">

            <label class="form-label">{{ \App\Helpers\translate('type_holdays_status') }}</label>
            <select id="holdays_status" class="form-select form-select-solid rounded-0 border-start border-end  w-200px"
                data-control="holdays_status" data-placeholder="{{ \App\Helpers\translate('type_holdays_status') }}">
                <option value="">{{ \App\Helpers\translate('choose') }}</option>

                <option value="1">{{ \App\Helpers\translate('underrevion_total_aholdays') }}</option> 
                <option value="0">{{ \App\Helpers\translate('waiting_total_aholdays') }}</option> 
                <option value="2">{{ \App\Helpers\translate('accepted_total_aholdays') }}</option> 
                <option value="3">{{ \App\Helpers\translate('ended_total_advances') }}</option> 

            </select>
        </div>

        <div class="mb-0 mx-2">

            <label class="form-label">{{ \App\Helpers\translate('type_holiday') }}</label>
            <select id="holdays_type" class="form-select form-select-solid rounded-0 border-start border-end  w-200px"
                data-control="holdays_type" data-placeholder="{{ \App\Helpers\translate('type_holiday') }}">
                <option value=" ">{{ \App\Helpers\translate('choose') }}</option>
                @php $vacations =  Helpers::get_vacations() @endphp
                @foreach ($vacations as $item)
                    <option value="{{ $item->id }}">
                        <?= $item->{'name_' . \App\Helpers\translate('lang')} ?>
                    </option>
                @endforeach
            </select>
        </div>
    @endif
</div>
