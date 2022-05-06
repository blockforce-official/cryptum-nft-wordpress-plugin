
(function ($) {
  $('.user-wallet-generator-button').click(function (event) {
    event.preventDefault();

    const web3 = new Web3();
    const account = web3.eth.accounts.create();

    console.log(account);
    $('#user-wallet-modal-address').text(account.address);
    $('#user-wallet-modal-privateKey').text(account.privateKey);
    $('#user-wallet-generator-modal').dialog({
      modal: true,
      dialogClass: 'no-close',
      width: 500,
      buttons: {
        'Save': function () {
          $.ajax({
            method: 'POST',
            url: '/wp-admin/admin-ajax.php',
            data: {
              action: 'save_user_meta',
              address: account.address,
            },
            success: (data) => {
              $('#user_wallet_address').val(account.address);
              $(this).dialog("close");
            },
            error: (xhr, status, error) => { console(error); },
          });
        },
        'Cancel': function () {
          $(this).dialog('close');
        }
      }
    });
  });
})(jQuery);
