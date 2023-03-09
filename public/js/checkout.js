
function showLoadingIcon(show = true) {
  jQuery('.loading-icon').css('display', show ? 'block' : 'none');
  jQuery('#user-wallet-connection-button').css('display', show ? 'none' : 'flex');
}
(function ($) {
  function showSignModal(address) {
    $('#wallet-sign-modal').dialog({
      modal: true,
      dialogClass: 'no-close no-title',
      buttons: {
        [checkout_wpScriptObject['cancel']]: function () {
          showLoadingIcon(false);
          disconnectWallet();
          $(this).dialog('close');
        },
        [checkout_wpScriptObject['sign']]: function () {
          signWithWallet(address)
            .then(({ address, signature }) => {
              console.log(address, signature);
              showLoadingIcon(false);

              $('#user-walletconnect-info').text(checkout_wpScriptObject['walletConnectedMessage']);
              $('#user-wallet-connection-button').css('display', 'none');
              setTimeout(() => {
                $('#user-wallet-connection-button').css('display', 'flex');
                $('#user-walletconnect-info').text('');
              }, 15000);

              $(this).dialog('close');
              $.ajax({
                method: 'POST',
                url: checkout_wpScriptObject.ajaxUrl,
                data: {
                  security: checkout_wpScriptObject.security,
                  action: checkout_wpScriptObject.action,
                  address,
                },
                success: (data) => {
                  $('#user_eth_wallet_address').val(address);
                },
                error: (xhr, status, error) => {
                  console.log(error);
                  $('#user_eth_wallet_address').val(address);
                },
              });
            }).catch(e => {
              console.error(e);
              // alert(e && e.message);
              showLoadingIcon(false);
            });
        },
      }
    });
  }

  initWalletConnection();

  $('#user-wallet-connection-button').click(function (event) {
    event.preventDefault();
    $('#user_eth_wallet_address').val('');

    showLoadingIcon(true);
    connectWithWallet()
      .then(address => delay(1000).then(() => showSignModal(address)))
      .catch(e => {
        console.error(e);
        showLoadingIcon(false);
      });
  });

  // $('#user-wallet-generator-button').click(function (event) {
  //   event.preventDefault();

  //   const web3 = new Web3();
  //   const account = web3.eth.accounts.create();

  //   $('#user-wallet-modal-address').text(account.address);
  //   $('#user-wallet-modal-privateKey').text(account.privateKey);
  //   $('#user-wallet-generator-modal').dialog({
  //     modal: true,
  //     dialogClass: 'no-close no-title',
  //     buttons: {
  //       [checkout_wpScriptObject['cancel']]: function () {
  //         $(this).dialog('close');
  //       },
  //       [checkout_wpScriptObject['save']]: function () {
  //         $.ajax({
  //           method: 'POST',
  //           url: checkout_wpScriptObject.ajaxUrl,
  //           data: {
  //             security: checkout_wpScriptObject.security,
  //             action: checkout_wpScriptObject.action,
  //             address: account.address,
  //           },
  //           success: (data) => {
  //             $('#user_eth_wallet_address').val(account.address);
  //             $(this).dialog("close");
  //           },
  //           error: (xhr, status, error) => {
  //             console.log(error);
  //             $('#user-wallet-modal-error').text(error);
  //             $('#user-wallet-modal-error').css('display', 'block');
  //             $('#user_eth_wallet_address').val(account.address);
  //             $(this).dialog("close");
  //           },
  //         });
  //       },
  //     }
  //   });
  // });
})(jQuery);
