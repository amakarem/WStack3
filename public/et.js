try {
    if (typeof sessionToken == 'string') {
        sessionToken = JSON.parse(atob(sessionToken));
        if (typeof sessionToken['eth_address'] !== 'undefined') {
            var eth_address = sessionToken['eth_address'];
        }
    }
    if (document.getElementById("sessionToken")) {
        document.getElementById("sessionToken").remove();
        delete sessionToken;
    }
} catch (err) {
    console.log(err.message);
}

if (window.ethereum) {
    var Balance = 0;
    var GasPrice = 0;
    var networkID = 0;
    let script = document.createElement('script');
    script.async = true;
    script.id = 'web3js';
    script.src = 'https://cdn.jsdelivr.net/npm/web3@4.16.0/dist/web3.min.js';
    //if (typeof eth_address == 'string') {
    script.setAttribute('onload', 'initWeb3();');
    window.ethereum.on('accountsChanged', async () => {
        initWeb3();
    });
    //}
    let firstScript = document.getElementsByTagName('script')[0];
    firstScript.parentNode.appendChild(script);
    networkChains();
}
var autrefresh = false;
async function eth_refresh() {
    setInterval(function () {
        autrefresh = true;
        initWeb3();
    }, 10000);
}


function search(input) {
  input = input.value;
  if (input.length < 3) {
    return false;
  }
  let URL = '/search/' + input;
  if (document.getElementById('localmarket') !== null && document.getElementById('localmarket').checked == true && typeof exchange !== 'undefined') {
    URL = URL + "?exchange=" + exchange;
  }
  let textClass = 'text-end';
  if (language.indexOf('ar') >= 0) {
    textClass = 'text-start';
  }
  const xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      var container = document.getElementById("swapsearchModal-result");
      container.innerHTML = '';
      let result = JSON.parse(this.responseText);
      if (result.length < 1) {
        container.innerHTML = '<p class="text-center">' + trans('No symbols match your criteria') + '</p>';
      } else {
        let i = 0;
        Object.keys(result).forEach(function (key) {
          i++;
          let row = document.createElement("div");
          let link = document.createElement("a");
          link.setAttribute('class', 'nav-link');
        
          row.setAttribute('class', 'row border-bottom p-2');
          if (i == result.length) {
            row.setAttribute('class', 'row p-2');
          }
          let col1 = document.createElement("div");
          col1.innerHTML = '<strong>' + result[key].symbol + '</strong>';
          col1.setAttribute('class', 'col col-xs-3 col-sm-3');
          let col2 = document.createElement("div");
          col2.setAttribute('class', 'col col-xs-5 col-sm-5');
          col2.innerHTML = result[key].price;
          let col3 = document.createElement("div");
          col3.setAttribute('class', 'col col-xs-4 col-sm-4 ' + textClass);
          let icon = '<img class="ico" src="' + result[key].logoURI + '">';

          col3.innerHTML = result[key].name + " " + result[key].balance + icon + favbtn;
          row.appendChild(col1);
          row.appendChild(col2);
          row.appendChild(col3);
          link.appendChild(row);
          container.appendChild(link);
        });
      }
    } else {
      return false;
    }
  };
  xhttp.open("GET", URL);
  xhttp.send();
}

async function getall() {
    if (document.getElementById("web3_wallet_1inch")) {
        let url = '/web3/wallet/' + eth_address;
        let response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                address: eth_address,
                _token: csrf_token
            })
        });

        let data = await response.json();
        // console.log(data);
        document.getElementById("web3_wallet_1inch").innerHTML = "<th>Token</th><th>Balance</th><th>Price</th>";
        for (const address in data) {
            if (data.hasOwnProperty(address)) {
                const token = data[address];
                document.getElementById("web3_wallet_1inch").innerHTML += '<tr><th><img class="ico" src="' + token.logoURI + '">' + token.symbol + '</th><td class="">' + token.balance + '</td><td class="">' + token.price + '</td></tr>';
                // console.log("Address:", token.address);
                // console.log("Symbol:", token.symbol);
                // console.log("Name:", token.name);
                // console.log("Balance:", token.balance);
                // console.log("Price:", token.price);
                // console.log("---");
            }
        }
        //return data;
    }
}

async function initWeb3() {
    const web3 = new Web3(window.ethereum);
    const accounts = await web3.eth.getAccounts();
    if (typeof accounts == 'object') {
        eth_address = accounts[0];
        console.log("detected");
    }
    if (typeof eth_address == 'string') {
        try {
            let loginbtn = document.getElementById("web3login");
            loginbtn.innerHTML = eth_address.slice(-4) + "***" + eth_address.slice(-4);
            loginbtn.setAttribute("disabled", true);
        } catch (err) {
            console.log(err.message);
        }
        let oldBalance = Balance;
        let oldGas = GasPrice;
        let accountKey = accounts.findIndex(item => eth_address.toLowerCase() === item.toLowerCase());
        if (accountKey >= 0) {
            Balance = await web3.eth.getBalance(eth_address);
            Balance = web3.utils.fromWei(Balance, 'ether');
            if (Balance === '0.') {
                Balance = '0.0';
            }
            GasPrice = await web3.eth.getGasPrice();
            GasPrice = web3.utils.fromWei(GasPrice, 'gwei');
            lowestgas = GasPrice;
            networkID = await web3.eth.net.getId();
            networkID = networkID.toString();
            let web3_networks = false;
            if (document.getElementById("web3_networks")) {
                web3_networks = true;
            }
            //if (document.getElementById("web3_wallet")) {
                i = 0;
                chains.forEach((network) => {
                    if (network.chainId == networkID) {
                        network_name = network.name + ' (' + networkID + ')';
                        //accounts[accountKey]
                        if (document.getElementById("web3_wallet")) {
                            document.getElementById("web3_wallet").innerHTML = '<tr><th>' + network.name + ' (' + networkID + ')</th><td class="' + GreenOrRed(oldBalance, Balance) + '"><img class="ico" src="/images/icons/' + network.nativeCurrency.symbol.toLowerCase() + '.svg"> ' + Balance + '</td><td class="' + GreenOrRed(oldGas, GasPrice) + '">' + GasPrice + ' Gwei</td></tr>';
                            if (autrefresh === false) {
                                eth_refresh();
                            }
                        }
                        return;
                    } else if (web3_networks == true && i <= 20 && typeof network.rpc[0] === 'string' && !network.rpc[0].includes('$') && network.nativeCurrency.symbol == 'ETH') {
                        i++;
                        console.log(network.name + " : " + network.rpc[0]);
                        NetworkGas(network.name, network.rpc[0], 'web3_networks');
                    } else {
                        return;
                    }
                });
            //}
            if (document.getElementById("eth_chain") && typeof network_name != 'undefined') {
                document.getElementById("eth_chain").innerHTML = network_name;
            }
            if (document.getElementById("eth_balance")) {
                document.getElementById("eth_balance").innerHTML = Balance;
            }
            if (document.getElementById("eth_gas")) {
                document.getElementById("eth_gas").innerHTML = GasPrice;
            }
        } else {
            try {
                let loginbtn = document.getElementById("web3login");
                loginbtn.innerHTML = "Connect Wallet";
                loginbtn.removeAttribute("disabled");
                document.getElementById('logout-form').submit();
            } catch (err) {
                console.log(err.message);
            }
        }
    }
}

async function NetworkGas(name, node, table) {
    if (!document.getElementById(table)) {
        return;
    }
    const web3 = new Web3(node);
    if (typeof web3 !== 'undefined') {
        try {
            table = document.getElementById(table);
            let GasPrice = await web3.eth.getGasPrice();
            GasPrice = web3.utils.fromWei(GasPrice, 'gwei');
            if (GasPrice !== 0) {
                let tr = document.createElement('tr');
                let th = document.createElement('th');
                th.innerHTML = name;
                tr.appendChild(th);
                let td = document.createElement('td');
                td.innerHTML = GasPrice;
                tr.appendChild(td);
                table.appendChild(tr);
            }
        } catch (err) {
            console.log(err.message);
        }
    } else {
        return;
    }
}


async function logoutWeb3() {
    if (!window.ethereum) {
        alert('MetaMask not detected. Please try again from a MetaMask enabled browser.')
    }
    const web3 = new Web3(window.ethereum);
    web3.eth.currentProvider.disconnect()
}

async function loginWeb3() {
    if (!window.ethereum) {
        alert('MetaMask not detected. Please try again from a MetaMask enabled browser.');
        return;
    }

    const web3 = new Web3(window.ethereum);
    const authenticateUrl = '/auth/web3/authenticate';
    const redirectUrl = '/';

    const message = "Please sign me in to (https://eth.onyxberg.us).";
    const hexMessage = web3.utils.utf8ToHex(message);

    const address = (await web3.eth.requestAccounts())[0];

    const signature = await window.ethereum.request({
        method: 'personal_sign',
        params: [hexMessage, address],
    });

    const csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const response = await fetch(authenticateUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            message: message,
            address: address,
            signature: signature,
            _token: csrf_token
        })
    });

    const data = await response.json();

    if (data.success) {
        window.location.href = redirectUrl;
    } else {
        alert("Login failed: " + (data.message || "Unknown error."));
        console.error("Login error:", data);
    }
}

async function networkChains() {
    let src = "/web3/chains.json";
    response = await fetch(src, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    });
    chains = await response.text();
    try {
        chains = JSON.parse(chains);
    } catch (err) {
        chains = [];
    }
    return chains;
}

function GreenOrRed(oldValue, newValue) {
    let colorClass = '';
    if (oldValue > newValue) {
        colorClass = 'text-danger';
    } else if (oldValue < newValue) {
        colorClass = 'text-success';
    }
    return colorClass;
}
