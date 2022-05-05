
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
          $(this).dialog("close");
        },
        'Cancel': function () {
          $(this).dialog('close');
        }
      }
    });
  });
})(jQuery);
