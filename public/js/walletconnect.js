var walletConnectProvider = null;

async function delay(ms) {
  return new Promise(resolve => setTimeout(() => resolve(1), ms));
}

function getProvider() {
  return walletConnectProvider;
}

async function connectWithWalletConnect() {
  if (walletConnectProvider && walletConnectProvider.connected) {
    await walletConnectProvider.disconnect();
    walletConnectProvider = null;
  }

  await delay(1500);

  walletConnectProvider = new WalletConnectProvider.default({
    rpc: {
      1: 'https://rpc.ankr.com/eth',
      4: 'https://rpc.ankr.com/eth_rinkeby',
      44787: "https://alfajores-forno.celo-testnet.org",
      42220: "https://forno.celo.org",
    },
  });

  await walletConnectProvider.enable();
  // console.log(walletConnectProvider);

  walletConnectProvider.on("accountsChanged", (accounts) => {
    console.log(accounts);
  });
  walletConnectProvider.on("chainChanged", (chainId) => {
    console.log(chainId);
  });

  walletConnectProvider.on("disconnect", (code, reason) => {
    console.log(code, reason);
    jQuery('#user_wallet_address').val('');
  });
  return walletConnectProvider.accounts[0];
}

async function signWithWalletConnect(address) {
  const message = wpScriptObject['signMessage'] + wpScriptObject['nonce'];
  const signature = await walletConnectProvider.request({
    method: 'personal_sign',
    params: [message, address],
  });
  return { address, signature };
}