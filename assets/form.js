document.addEventListener('DOMContentLoaded', function(e){
    let cartoEventForm = document.getElementById('cartoevent-form');

    cartoEventForm.addEventListener('submit', (e) =>{
        e.preventDefault();

        //reset the form messages
        resetMessages();

        //collect all the data
        let data = {
            // name: cartoEventForm.querySelector('[name="name"]').value,
            // email: cartoEventForm.querySelector('[name="email"]').value,
            title: cartoEventForm.querySelector('[name="title"]').value,
            date_event: cartoEventForm.querySelector('[name="date_event"]').value,
            description: cartoEventForm.querySelector('[name="description"]').value,
            adresse: cartoEventForm.querySelector('[name="adresse"]').value,
            type_event: cartoEventForm.querySelector('[name="type_event"]').value,
            ref_gf: cartoEventForm.querySelector('[name="ref_gf"]').value,
            nonce: cartoEventForm.querySelector('[name="nonce"]').value,
        }

        // //validate everything
        // if (! validateEmail(data.email)) {
        //     cartoEventForm.querySelector('[data-error="invalidEmail"]').classList.add('show');
        //     return;
        // }

        
        //Ajax http post request
        let url = cartoEventForm.dataset.url;
        let params = new URLSearchParams(new FormData(cartoEventForm));
        
        cartoEventForm.querySelector('.js-form-submission').classList.add('show');

        fetch(url, {
            method: "POST",
            body: params
        }).then(res => res.json())
            .catch(error => {
                resetMessages();
                cartoEventForm.querySelector('.js-form-error').classList.add('show');
            })
            .then(response => {
                resetMessages();
                //deal with the response
                if(response === 0 || response.status === 'error'){
                    cartoEventForm.querySelector('.js-form-error').classList.add('show');
                    return;
                }

                cartoEventForm.querySelector('.js-form-success').classList.add('show');
                cartoEventForm.reset();
            })
    });

});

function resetMessages(){
    document.querySelectorAll('.field-msg').forEach(f => f.classList.remove('show'));
}