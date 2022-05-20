
(function ($) {
  $('.user-wallet-generator-button').click(function (event) {
    event.preventDefault();

    const web3 = new Web3();
    const account = web3.eth.accounts.create();

    $('#user-wallet-modal-address').text(account.address);
    $('#user-wallet-modal-privateKey').text(account.privateKey);
    $('#user-wallet-generator-modal').dialog({
      modal: true,
      dialogClass: 'no-close',
      width: 500,
      buttons: {
        [objectL10n['save']]: function () {
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
            error: (xhr, status, error) => {
              $('#user-wallet-modal-error').text(error);
              $('#user-wallet-modal-error').css('display', 'block');
              console(error);
            },
          });
        },
        [objectL10n['cancel']]: function () {
          $(this).dialog('close');
        }
      }
    });
  });
})(jQuery);
