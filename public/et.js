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
    if (typeof eth_address == 'string') {
        script.setAttribute('onload', 'initWeb3();');
        window.ethereum.on('accountsChanged', async () => {
            initWeb3();
        });
    }
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

async function initWeb3() {
    const web3 = new Web3(window.ethereum);
    const accounts = await web3.eth.getAccounts();
    if (typeof accounts == 'object')
    {
        eth_address = accounts[0];
    }
    if (typeof eth_address == 'string') {
        console.log("connected");
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
            if (document.getElementById("web3_wallet")) {
                i = 0;
                chains.forEach((network) => {
                    if (network.chainId == networkID) {
                        document.getElementById("web3_wallet").innerHTML = '<tr><th>' + network.name + ' (' + networkID + ')</th><td>' + accounts[accountKey] + '</td><td class="' + GreenOrRed(oldBalance, Balance) + '"><img class="ico" src="/images/icons/' + network.nativeCurrency.symbol.toLowerCase() + '.svg"> ' + Balance + '</td><td class="' + GreenOrRed(oldGas, GasPrice) + '">' + GasPrice + ' Gwei</td></tr>';
                        if (autrefresh === false) {
                            eth_refresh();
                        }
                        return;
                    } else if (web3_networks == true && i <= 20 && typeof network.rpc[0] === 'string' && !network.rpc[0].includes('$') && network.nativeCurrency.symbol == 'ETH') {
                        i++;
                        //console.log(network.name + " : " + network.rpc[0]);
                        //NetworkGas(network.name, network.rpc[0], 'web3_networks');
                    } else {
                        return;
                    }
                });
            }
            if (document.getElementById("eth_balance")) {
                document.getElementById("eth_balance").innerHTML = Balance;
            }
            if (document.getElementById("eth_gas")) {
                document.getElementById("eth_gas").innerHTML = GasPrice;
            }
        } else {
            try {
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
        alert('MetaMask not detected. Please try again from a MetaMask enabled browser.')
    }

    const web3 = new Web3(window.ethereum);

    const authenticateUrl = '/auth/web3/authenticate';
    const redirectUrl = '/';

    const message = "Please sign me in to (https://eth.onyxberg.us).";
    const address = (await web3.eth.requestAccounts())[0];
    const signature = await web3.eth.personal.sign(message, address, csrf_token);

    response = await fetch(authenticateUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            'message': message,
            'address': address,
            'signature': signature,
            '_token': csrf_token
        })
    });
    const data = await response.text();
    if (data) {
        window.location = '/';
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
