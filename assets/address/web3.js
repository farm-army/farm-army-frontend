(function($){
    $(function(){
        const sendAction = async (web3Data, accountAddress, accountChainId, tokenPrice, msgCallback) => {
            const inputs = (web3Data.inputs || []).map((input, index) => {
                if (input === true || input === false) {
                    return {
                        "internalType": "bool",
                        "name": "_parameter_" + index,
                        "type": "bool"
                    }
                } else if (Number.isInteger(input)) {
                    return {
                        "internalType": "uint256",
                        "name": "_parameter_" + index,
                        "type": "uint256"
                    }
                }

                return {};
            });

            const ABI = [
                {
                    "inputs": inputs,
                    "name": web3Data.method,
                    "outputs": [],
                    "stateMutability": "nonpayable",
                    "type": "function"
                }
            ];

            msgCallback('Detecting Web3...');

            const provider = await detectEthereumProvider();
            if (!provider) {
                msgCallback('Error: No Web3 provider found...');
                return;
            }

            msgCallback('Checking Accounts...');
            const accounts = await provider.request({method: 'eth_requestAccounts'});

            if (accounts.length === 0) {
                msgCallback('No Web3 account found');
                return;
            }

            if (accounts[0].toLowerCase() !== accountAddress.toLowerCase()) {
                msgCallback(`Found wrong Web3 account: ${accounts[0].toLowerCase()} != ${accountAddress.toLowerCase()}`);
                return;
            }

            msgCallback('Checking Chain...');

            const chainId = parseInt(await provider.request({method: 'eth_chainId'}), 16);

            if (!chainId || accountChainId !== chainId) {
                msgCallback(`Wrong Web3 chain selected: ${accountChainId} != ${chainId ? chainId : 'n/a'}`);

                const newChainIdHex = '0x' + accountChainId.toString(16);
                try {
                    await provider.request({method: 'wallet_switchEthereumChain', params: [{chainId: newChainIdHex}]});
                } catch (e) {
                    msgCallback(`Chain (${accountChainId}) change request error: ${e.message}`);
                    return;
                }

                const chainId = parseInt(await provider.request({method: 'eth_chainId'}), 16);
                if (accountChainId !== chainId) {
                    msgCallback(`Still wrong Web3 chain selected: ${accountChainId} != ${chainId}`);
                    return;
                }

                msgCallback(`Chain changed to: ${accountChainId}`);
            }

            const web3 = new Web3(provider);
            const contract = new web3.eth.Contract(ABI, web3Data.contract)

            let tx;
            try {
                tx = await contract.methods[web3Data.method](...(web3Data.inputs || []));
            } catch (e) {
                console.log('error', e)
                return;
            }

            msgCallback('Estimate Transaction Gas...');

            let estimateGas
            try {
                estimateGas = await tx.estimateGas({from: accounts[0]});
            } catch (e) {
                msgCallback(`Transaction error estimateGas: ${e.message}`);
                return;
            }

            msgCallback('Get gas price...');
            const gasPrice = await web3.eth.getGasPrice();
            const txCost = (web3.utils.toBN(estimateGas) * web3.utils.toBN(gasPrice)) / 1e18;

            if (tokenPrice) {
                const usdPrice = txCost * tokenPrice;
                msgCallback(`Estimate tx price: ${txCost.toFixed(8)} / $${usdPrice.toFixed(2)}`);
            } else {
                msgCallback(`Estimate tx price: ${txCost.toFixed(8)}`);
            }

            const txData = {
                from: accounts[0],
                to: web3Data.contract,
                data: tx.encodeABI(),
                gas: estimateGas,
                gasPrice: web3.utils.toHex(gasPrice),
            };

            msgCallback('Sending Transaction...');

            let sendTransaction;
            try {
                sendTransaction = await web3.eth.sendTransaction(txData)
                    .on('transactionHash', (hash) => {
                        msgCallback(`Transaction placed: ${hash}`);
                        msgCallback(`Waiting for confirmation...`);
                    });
            } catch (e) {
                msgCallback(`Transaction error: ${e.message}`);
                return;
            }

            msgCallback(`Yeah Done!`);
        }

        $("body").on("click", ".modal .web3-action", async function(e) {
            e.preventDefault();

            const web3Data = JSON.parse($(this).attr('data-web3'));

            const web3Container = $(this).closest('.web3-action-container');
            const message = web3Container.find('.message');

            const accountAddress = web3Container.attr('data-web3-account');
            const chainId = parseInt(web3Container.attr('data-web3-chain-id'));
            const tokenPrice = web3Container.attr('data-web3-token-price');

            const msgCallback = (text) => {
                message.html((message.html() + "\n" + text).trim());
            }

            // init
            web3Container.find('.web3-spinner').removeClass('visually-hidden');
            message.empty();
            message.closest('.card').removeClass('visually-hidden');

            await sendAction(web3Data, accountAddress, chainId, tokenPrice, msgCallback);
            web3Container.find('.web3-spinner').addClass('visually-hidden');
        });
    });
})(jQuery);
