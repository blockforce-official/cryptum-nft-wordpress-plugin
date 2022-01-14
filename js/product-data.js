(function ($) {

  function handleNFTEnableCheckbox() {
    if ($("#_cryptum_nft_options_nft_enable").is(":checked")) {
      $("#_cryptum_nft_options_product_id").attr("required", true);
      $("#cryptum_nft_options_div").show();
    } else {
      $("#_cryptum_nft_options_product_id").attr("required", false);
      $("#cryptum_nft_options_div").hide();
    }
  }

  $("#_cryptum_nft_options_nft_enable").click(function () {
    handleNFTEnableCheckbox();
  });
  handleNFTEnableCheckbox();
})(jQuery);
