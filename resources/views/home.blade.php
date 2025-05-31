@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}

                    <div>
                                        <div class="mt-16">
                    <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                        <a id="web3login" class="btn btn-warning" onclick="loginWeb3()" style="background: #ffffff;padding: 10px;">{{ __('Connect MetaMask') }}</a>
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
                    </div>
                </div>
            </div>
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
        <button type="button" class="btn btn-danger" onclick="getall(true)">Load all tokens</button>
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
        <button class="btn btn-primary" onclick="swapnow()">Swap</button>
        <button class="btn btn-secondary" data-bs-target="#swapquoteModalToggle" data-bs-toggle="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
@endsection
