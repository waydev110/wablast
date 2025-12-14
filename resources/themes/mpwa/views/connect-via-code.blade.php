<x-layout-dashboard title="{{__('Scan')}} {{ $number->body }}">

    <h4 class="">{{ __('Whatsapp Account :number', ['number' => $number->body]) }}</h4>



    <div class="alert border-0 bg-light-info alert-dismissible fade show py-2">
        <div class="d-flex align-items-center">
            <div class="fs-3 text-info">
                {{-- icon info --}}
                <i class="bi bi-info-circle-fill"></i>
            </div>
            <div class="ms-3">
                <div class="text-info">{{__('Dont leave your phone before connencted')}}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card widget widget-stats-large">
                <div class="row">
                    <div class="col-xl-8">
                        <div class="widget-stats-large-chart-container">
                            <div class="card-header logoutbutton">


                            </div>
                            <div class="card-body">
                                <div id="apex-earnings"></div>
                                <div class="imageee text-center">
                                    @if (Auth::user()->is_expired_subscription)
                                        {{-- text --}}
                                        <img src="{{ asset('images/other/expired.png') }}" height="300px"
                                            alt="">
                                    @else
                                        <img src="{{ asset('assets/images/waiting.jpg') }}" height="300px"
                                            alt="">
                                    @endif
                                </div>
                                <div class="statusss text-center">
                                    @if (Auth::user()->is_expired_subscription)
                                        <button class="btn btn-danger   " type="button" disabled>
											{{__('Your subscription is expired. Please renew your subscription.')}}
                                        </button>
                                    @else
                                        <button class="btn btn-primary" type="button" disabled>
                                            <span class="spinner-grow spinner-grow-sm" role="status"
                                                aria-hidden="true"></span>
                                            {{__('Witing For node server..')}}
                                        </button>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="widget-stats-large-info-container">
                            <div class="card-header">
                                <h5 class="card-title">{{__('Whatsapp Info')}}<span
                                        class="badge badge-info badge-style-light">{{__('Updated 5 min ago')}}</span>
                                </h5>
                            </div>
                            <div class="card-body account">

                                <ul class="list-group account list-group-flush">
                                    <li class="list-group-item name">{{__('Name :')}} </li>
                                    <li class="list-group-item number">{{__('Number :')}} </li>
                                    <li class="list-group-item device">{{__('Device :')}} </li>

                                </ul>
                                {{-- <div class="card bg-dark text-white">
                                    <div class="card-body" style="height: 300px; overflow-y: scroll;">
                                        <p class="card-text">{{__('Log :')}}</p>
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-layout-dashboard>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // HTTP POLLING MODE - PAIRING CODE (SHARED HOSTING COMPATIBLE)
    const is_expired_subscription = '{{ Auth::user()->is_expired_subscription }}';
    if (!is_expired_subscription) {
        let device = '{{ $number->body }}';
        let pollingInterval = null;
        let isConnected = false;
        
        // Start connection via pairing code
        function startConnectionViaCode() {
            $.ajax({
                url: '{{ route("start.connection.code.http") }}',
                method: 'POST',
                data: {
                    device: device,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log('Pairing code generation initiated:', response);
                    startPolling();
                },
                error: function(xhr) {
                    console.error('Failed to start connection:', xhr);
                    $('.statusss').html(`<button class="btn btn-danger" type="button" disabled>
                        {{__('Failed to connect to server')}}
                    </button>`);
                }
            });
        }
        
        // Poll connection status every 2 seconds
        function startPolling() {
            if (pollingInterval) clearInterval(pollingInterval);
            
            pollingInterval = setInterval(function() {
                $.ajax({
                    url: '/{{ LaravelLocalization::getCurrentLocale() }}/poll-connection/' + device,
                    method: 'GET',
                    success: function(response) {
                        if (response.status && response.data) {
                            handleConnectionData(response.data);
                        }
                    },
                    error: function(xhr) {
                        console.error('Polling error:', xhr);
                    }
                });
            }, 2000); // Poll every 2 seconds
        }
        
        // Handle connection data from polling
        function handleConnectionData(data) {
            // If pairing code is available
            if (data.pairing_code && !isConnected) {
                $('.imageee').html(`<h2 class="text-primary">${data.pairing_code}</h2>`);
                $('.statusss').html(`<button class="btn btn-warning" type="button" disabled>
                    <span class="" role="status" aria-hidden="true"></span>
                    {{__('Enter this code in WhatsApp')}}
                </button>`);
            }
            
            // If device is connected
            if (data.device_status === 'Connected' && data.connection_data) {
                isConnected = true;
                clearInterval(pollingInterval);
                
                const connData = data.connection_data;
                $('.name').html(`{{__('Name :')}} ${connData.name || 'N/A'}`);
                $('.number').html(`{{__('Number :')}} ${connData.id || device}`);
                $('.device').html(`{{__('Device / Token : Not detected -')}} ${device}`);
                
                if (connData.ppUrl) {
                    $('.imageee').html(`<img src="${connData.ppUrl}" height="300px" alt="Profile">`);
                }
                
                $('.statusss').html(`<button class="btn btn-success" type="button" disabled>
                    <span class="" role="status" aria-hidden="true"></span>
                    {{__('Connected')}}
                </button>`);
                
                $('.logoutbutton').html(`<button class="btn btn-danger" class="logout" id="logout" onclick="logout()">
                    {{__('Logout')}}
                </button>`);
            }
        }
        
        // Logout function
        function logout() {
            $.ajax({
                url: '{{ env("WA_URL_SERVER") }}:{{ env("PORT_NODE") }}/logout-device-http',
                method: 'POST',
                data: { device: device },
                success: function() {
                    location.reload();
                },
                error: function() {
                    // Fallback to Laravel logout
                    $.ajax({
                        url: '/{{ LaravelLocalization::getCurrentLocale() }}/home',
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                            deviceId: '{{ $number->id }}'
                        },
                        success: function() {
                            location.reload();
                        }
                    });
                }
            });
        }
        
        // Start connection on page load
        startConnectionViaCode();
        
        // Make logout function global
        window.logout = logout;
    }
</script>
