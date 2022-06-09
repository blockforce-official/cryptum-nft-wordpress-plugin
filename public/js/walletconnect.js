var walletProvider = null;
var web3Modal = null;

async function delay(ms) {
  return new Promise(resolve => setTimeout(() => resolve(1), ms));
}

function initWalletConnection() {
  web3Modal = new window.Web3Modal.default({
    cacheProvider: false, // optional
    disableInjectedProvider: false, // optional. For MetaMask / Brave / Opera.
    providerOptions: {
      walletconnect: {
        package: window.WalletConnectProvider.default,
        options: {
          rpc: {
            1: 'https://rpc.ankr.com/eth',
            4: 'https://rpc.ankr.com/eth_rinkeby',
            44787: 'https://alfajores-forno.celo-testnet.org',
            42220: 'https://forno.celo.org',
          },
        },
      }
    }
  });
  console.log(web3Modal);
}

async function connectWithWallet() {
  window.localStorage.removeItem('walletconnect');
  window.localStorage.removeItem('WALLETCONNECT_DEEPLINK_CHOICE');
  await delay(1500);
  
  await web3Modal.clearCachedProvider();

  walletProvider = await web3Modal.connect();
  walletProvider.on("disconnect", (error) => {
    console.log(error);
  });

  const web3 = new window.Web3(walletProvider);
  const accounts = await web3.eth.getAccounts();
  if (!accounts || accounts.length === 0) {
    throw new Error('Error trying to connect wallet');
  }
  return accounts[0];
}

async function disconnectWallet() {
  window.localStorage.removeItem('walletconnect');
  window.localStorage.removeItem('WALLETCONNECT_DEEPLINK_CHOICE');
  if (walletProvider && walletProvider.disconnect) {
    await walletProvider.disconnect();
  }
  if (walletProvider && walletProvider.close) {
    await walletProvider.close();
  }
}

async function signWithWallet(address) {
  const message = walletconnection_wpScriptObject['signMessage'] + walletconnection_wpScriptObject['nonce'];
  const signature = await walletProvider.request({
    method: 'personal_sign',
    params: [message, address],
  });
  return { address, signature };
}