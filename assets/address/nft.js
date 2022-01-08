(function ($) {
    $(function () {
        $('.card-nft').each(async function () {
            const uri = $(this).data('token-uri');
            if (!uri) {
                $(this).find('.nft-spinner').addClass('d-none');
                return;
            }

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 15000);

            let nft;
            try {
                const response = await fetch(uri, {signal: controller.signal});
                nft = await response.json();
            } catch (e) {
                $(this).find('.nft-attributes').html(`<span class="text-muted">Error fetching: ${uri}</span>`);
                return;
            } finally {
                clearTimeout(timeoutId);
                $(this).find('.nft-spinner').addClass('d-none');
            }

            if (nft.image) {
                const imageHtml = $('<span>').text(nft.image).html();
                $(this).find('.nft-image').html(`<img class="img-thumbnail" style="max-width: 100%" loading="lazy" src="${imageHtml}">`);
            }

            if (nft.name) {
                const nameHtml = $('<span>').text(nft.name).html();
                $(this).find('.nft-name').html('(' + nameHtml + ')');
            }

            if (nft.description) {
                const descriptionHtml = $('<span>').text(nft.description).html();
                $(this).find('.nft-description').html(' <span class="fas fa-question-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="' + descriptionHtml + '"></span>');
            }

            if (nft.attributes) {
                let content = '';

                for (const [key, value] of Object.entries(nft.attributes)) {
                    let keyHtml = '';
                    let valueHtml = '';

                    if (typeof value !== 'object') {
                        keyHtml = $('<span>').text(key).html();
                        valueHtml = $('<span>').text(value).html();
                    } else if (value.trait_type) {
                        keyHtml = $('<span>').text(value.trait_type).html();
                        valueHtml = $('<span>').text(value.value).html();
                    }

                    if (keyHtml || valueHtml) {
                        content += `<tr><td>${keyHtml}</td><td>${valueHtml}</td></tr>`;
                    }
                }

                $(this).find('.nft-attributes').html(`<table class="table table-small-padding"><tbody>${content}</tbody></table>`);
            }
        });
    });
})(jQuery);
