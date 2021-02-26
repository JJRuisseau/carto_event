<form id="cartoevent-form" action="#" method="post" data-url="<?php echo admin_url('admin-ajax.php'); ?>">
	<div class="field-container">
		<label for="titre_event">Titre de l'événement</label>
		<input id="titre_event" type="text" placeholder="Titre de l'événement" id="title" name="title" required><br />
		<small class="field-msg error" data-error="invalidTitle">Le titre de l'événement est obligatoire</small>
	</div>

	<div class="field-container">
		<label for="date_event">Date de l'événement</label>
		<input type="datetime-local" id="date_event" name="date_event" required><br />
		<small class="field-msg error" data-error="invalidDate">La date de l'événement est obligatoire</small>
	</div>

	<div class="field-container">
		<label for="description">Description</label>
		<textarea name="description" id="description" class="field-input" placeholder="Description de l'évènement" ></textarea><br />
		<small class="field-msg error" data-error="invalidMessage">Une description est obligatoire</small>
	</div>

	<div class="field-container">
		<label for="adresse">Description</label>
		<textarea name="adresse" id="adresse" class="field-input" placeholder="Ex : Place nationale Montauban" required></textarea>
		<em>Adresse exacte du lieu de l'événement. </em><br />
		<small class="field-msg error" data-error="invalidMessage">Une adresse exacte est obligatoire</small>
	</div>

	<div class="field-container">
		<label>Type d'événement : </label><br>
		<input type="radio" id="type_event_1" name="type_event" value="Réunion publique" required>
		<label for="type_event_1">Réunion publique</label><br />
		<input type="radio" id="type_event_2" name="type_event" value="Table d'information" required>
		<label for="type_event_2">Table d'information</label><br />
		<input type="radio" id="type_event_3" name="type_event" value="Manifestation" required>
		<label for="type_event_3">Manifestation</label>
		</div>
		<div class="field-container">
		<label>Référendum Frexit / Génération Frexit : </label><br>
		<input type="radio" id="ref_gf_1" name="ref_gf" value="ref" required>
		<label for="ref_gf_1">Référendum Frexit</label><br />
		<input type="radio" id="ref_gf_2" name="ref_gf" value="gf" required >
		<label for="ref_gf_2">Génération Frexit</label><br />
	</div>
	
	<div class="field-container">
		<div>
            <button type="stubmit" class="btn btn-default btn-lg btn-sunset-form">Soumettre</button>
        </div>
		<small class="field-msg js-form-submission">Soumission du formulaire en cours, Veuillez patienter</small>
		<small class="field-msg success js-form-success">Message envoyé avec succès, Merci !</small>
		<small class="field-msg error js-form-error">Il y a un problème avec le formulaire, veuillez réessayer !</small>
	</div>

	<input type="hidden" name="action" value="submit_cartoevent" />
	<input type="hidden" name="nonce" value="<?php echo wp_create_nonce("cartoevent-nonce"); ?>" />

</form>