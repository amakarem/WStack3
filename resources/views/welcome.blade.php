<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Laravel web3 dex</title>
        <script>
            const csrf_token = '{{ csrf_token() }}';
        </script>
        <script id='web3' src="{{ asset('et.js?v=1.52') }}"></script>
        <script src="https://kit.fontawesome.com/610cb3f14b.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
         <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
        <style>
            .ico {
                height: 1em;
            }
            
        </style>
    </head>
    <body class="antialiased" id="app">
        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">

            <div class="max-w-7xl mx-auto p-6 lg:p-8">
                <div class="flex justify-center">
                    <img src="images/icons/eth.svg" style="max-width: 6rem;">
                </div>

                <div class="mt-16">
                    <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                    @auth
                        <a href="{{ route('logout') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log Out</a>
                    @else
                        <a id="web3login" class="btn btn-warning" onclick="loginWeb3()" style="background: #ffffff;padding: 10px;">{{ __('Connect MetaMask') }}</a>
                    @endauth
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                        
                        <div class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                            <div>
                                <h2 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white"><i class="fa-brands fa-ethereum text-primary"></i> <span id="eth_balance">0</span></h2>
                                <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed" ><span id="eth_chain"></span> <i class="fa-solid fa-gas-pump text-info"></i> <span id="eth_gas">0</span></p>
                            </div>
                        </div>

                    </div>
                </div>
                <button type="button" onclick="getall();" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
  Launch 1inch Swap
</button>
                <div class="flex justify-center mt-16 px-0 sm:items-center sm:justify-between">
                    <div class="text-center text-sm sm:text-left">
                        &nbsp;
                    </div>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>

<!-- Modal -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="staticBackdropLabel">1inch Swap</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="web3_wallet_1inch_spinner" class="spinner-border m-5" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <table id="web3_wallet_1inch" class="table table-striped"></table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="swapquoteModalToggle2" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true" aria-labelledby="swapquoteModalToggleLabel2" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="swapquoteModalToggleLabel2">Swap</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table">
            <tr><th>From <span id="swapFrom"></span></th><td><input type="text" value="0.0" id="swapAmount"></td></tr>
            <tr><th>To <span id="swapTo"></span></th><td><input type="text" value="0.0" id="swapAmountVal" disabled></td></tr>
        </table>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" data-bs-target="#swapquoteModalToggle" data-bs-toggle="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
    </body>
</html>
