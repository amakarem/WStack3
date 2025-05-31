      <style>
        .countryicon {
            max-width: 1rem;
        }
        .result div:hover{background-color:gray;}
        .align-unset {
          align-items:unset;
        }
      </style>
      <!-- Modal -->
      <div class="modal fade" id="swapsearch" tabindex="-1" aria-labelledby="swapsearchLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header align-unset">
              <div class="sticky-top d-block w-100">
                <h1 class="modal-title fs-4 mb-2" id="swapsearchLabel">{{ __('Search') }}</h1>
                <input class="form-control modal-title mb-2 " type="search" placeholder="{{ __('Search') }}" aria-label="Search" id="SearchInput" name="SearchInput" onkeyup="search(this)">
                <input type="radio" class="btn-check" name="market" id="localmarket" onclick="search(document.getElementById('SearchInput'))">
                <label id="localmarket-Label" for="localmarket" class="btn btn-outline-primary ">Local</label>
                <input type="radio" class="btn-check" name="market" id="allmarkets" onclick="search(document.getElementById('SearchInput'))">
                <label for="allmarkets" class="btn btn-outline-primary">{{ __('All Tokens') }}</label>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="swapsearch-body" class="modal-body">
              <div id="swapsearch-result" class="result"></div>
            </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="ReloadAfterSearch();">{{__('Close')}}</button>
              </div>
            </div>
          </div>
        </div>
      </div>
