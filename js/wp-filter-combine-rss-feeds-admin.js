var $j=jQuery.noConflict();$j(document).ready(function(){$j(document).on("click",".add-custom-row",function(){var t=parseInt($j(".bloc-form-custom").length),o=$j(".bloc-form-custom:last").clone().attr("id",t);o.find(".custom-filter-name").attr("name","wpfcrf_custom_feeds["+t+"][name]").attr("value",""),o.find(".custom-filter-value").attr("name","wpfcrf_custom_feeds["+t+"][value]").attr("value",""),o.find(".remove-custom-row").attr("name","remove-"+t),$j(".bloc-form-custom:last").after(o)}),$j(document).on("click",".remove-custom-row",function(){parseInt($j(".bloc-form-custom").length)>1&&$j(this).parents(".bloc-form-custom").remove()}),$j(".wpfcrf-clipboard").hide(),$j("#btn-wpfcrf").on("click",function(){var t=$j("<textarea>");$j("body").append(t),t.val($j(this).attr("data-text")).select(),document.execCommand("copy"),t.remove(),$j(this).remove(),$j(".wpfcrf-clipboard").show()}),$j(".wpfcrf-form-clipboard").hide(),$j("#btn-wpfcrf-form").on("click",function(){var t=$j("<textarea>");$j("body").append(t),t.val($j(this).attr("data-text")).select(),document.execCommand("copy"),t.remove(),$j(this).remove(),$j(".wpfcrf-form-clipboard").show()})});