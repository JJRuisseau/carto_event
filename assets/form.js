document.addEventListener('DOMContentLoaded', function(e){
    let cartoEventForm = document.getElementById('cartoevent-form');

    cartoEventForm.addEventListener('submit', (e) =>{
        e.preventDefault();

        resetMessages();
        
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