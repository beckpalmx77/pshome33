<form id="checkoutTrueWallet" method="POST" action="omise/charge">

    <input type="hidden" name="omiseToken">

    <input type="hidden" name="omiseSource">

    <input type="text" name="money" value="300" class="form-control mb-3" placeholder="จำนวนเงิน">

    <button type="submit" id="checkoutButton" class="btn btn-primary">Checkout</button>

</form>


<script src="https://cdn.omise.co/omise.js"></script>

<script>

    $(document).ready(function () {

        OmiseCard.configure({

            publicKey: "pkey_test_xxxxxx" // ใส่ Public Key ที่ได้จาก Omise

        });

        let button = document.querySelector("#checkoutButton");

        let form = document.querySelector("#checkoutTrueWallet");


        button.addEventListener("click", (event) => {

            event.preventDefault();

            OmiseCard.open({

                amount: 300 * 100, // จำนวนเงิน (300) ต้องระบุเป็นหน่วยสตางค์

                currency: "THB",

                locale: "th",

                defaultPaymentMethod: "truemoney", // วิธีการชำระเงิน

                frameDescription: "ร้าน Matumweb",

                image: "img/logo.img",

                onCreateTokenSuccess: (nonce) => {

                    if (nonce.startsWith("tokn_")) {

                        form.omiseToken.value = nonce;

                    } else {

                        form.omiseSource.value = nonce;

                    }

                    form.submit();

                }

            });

        });

    });

</script>




