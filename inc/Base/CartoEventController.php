<?php
/** 
 * @package CartoEvent
 */

namespace Inc\Base;

use \Inc\Api\SettingsApi;
use \Inc\Base\BaseController;
use \Inc\Api\Callbacks\CartoEventCallbacks;

/**
 * 
 */
class CartoEventController extends BaseController
{
    public $settings;
    
    public $callbacks;

    /**
     * Création, paramétrage et enregistrement du plugin
     */
    public function register() 
    {
        $this->settings = new SettingsApi();

        $this->callbacks = new CartoEventCallbacks();

        add_action('init', array($this, 'event_cpt'));

        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));

        add_action('save_post', array($this,'save_meta_box'));

        $this->setShortcodePage();

        add_shortcode('evenement-form', array($this, 'evenement_form'));

        add_shortcode('cartoevent-leaflet', array($this, 'cartoevent_leaflet'));

        add_action('manage_evenement_posts_columns', array($this, 'set_custom_columns'));

        add_action('manage_evenement_posts_custom_column', array($this, 'set_custom_columns_data'), 10, 2);

        add_filter('manage_edit-evenement_sortable_columns',array($this,'set_custom_columns_sortable'));

        add_action('wp_ajax_submit_cartoevent', array($this, 'submit_cartoevent'));
        
        add_action('wp_ajax_nopriv_submit_cartoevent', array($this, 'submit_cartoevent'));

        add_action( 'manage_posts_extra_tablenav', array($this, 'admin_event_json_button'), 20, 1 );

        add_action( 'init', array($this, 'json_carto_export' ));

        add_action( 'pre_get_post', array($this, 'date_event_orderby'));
    }

    /**
     * Paramétrage et création des colonnes dans la liste des événements
     * @return $columns
     */
    public function set_custom_columns($columns) {
        $columns['author'] = 'Author';

        //$columns['approved'] = 'Validé ?';
        
        $columns['adresse'] = 'Adresse';
        
        $columns['date_event'] = 'Date et heure';

        $columns['dept'] = 'Département';
        
        $columns['ref_gf'] = 'Référendum / GF ?';
        
        $columns['type_event'] = 'Type d\'événement';

        return $columns;
    }

    /**
     * Paramétrage du contenu des colonnes de la liste des événements
     */
    public function set_custom_columns_data($column, $post_id) {

        $url_icon_ref = $this->plugin_url . 'assets/icon/icon_rf.png';
        
        $url_icon_gf = $this->plugin_url . 'assets/icon/icon_gf.png';

        $data = get_post_meta($post_id, '_carto_event_key', true);

        $adresse = isset($data['adresse']) ? $data['adresse'] : '';

        $dept = isset($data['dept']) ? $data['dept'] : '';
        
        $date = isset($data['date_event']) ? $data['date_event'] : '';
        
        $date_event = date('d/m/Y à H:i',strtotime($date));
        
        //$approved = isset($data['approved']) && $data['approved'] === 1 ? '<strong>Oui</strong>' : 'Non';
        
        $ref_gf = isset($data['ref_gf']) && $data['ref_gf'] === 'ref' ? '<img witdh="40" height="40" src="' . $url_icon_ref . '">' : '<img witdh="40" height="40" src="' . $url_icon_gf . '">';
        
        $type_event = isset($data['type_event']) ? $data['type_event'] : '';

        switch($column) {
            // case 'approved':
            //     echo $approved;
            //     break;
            case 'adresse':
                echo $adresse;
                break;
            case 'date_event':
                echo $date_event;
                break;
            case 'dept':
                echo $dept;
                break;
            case 'ref_gf':
                echo $ref_gf;
                break;
            case 'type_event':
                echo $type_event;
                break;
        }
    }

    /**
     * Paramétrages de tri/filtre dans la liste des événements
     * @return $columns
     */
    public function set_custom_columns_sortable($columns) {
        $columns['author'] = 'author';
        
        //$columns['approved'] = 'approved';
        
        $columns['adresse'] = 'adresse';
        
        $columns['date_event'] = 'date_event';
        
        $columns['dept'] = 'dept';

        return $columns;
    }

    /**
     * Ajout d'un bouton Générer JSON dans l'admin
     */
    public function admin_event_json_button($which) {
        global $typenow;

        if ( 'evenement' === $typenow && 'top' === $which ) {
            ?>
                <div class="alignleft actions custom">
                    <button type="submit" name="generate_json" class="button" value="yes"><?php
                    _e( 'Générer les points sur la carte'); ?></button>
                </div>
            <?php
        }
    }

    public function date_event_orderby( $query ) {
        if( ! is_admin() )
        return;

        $orderby = $query->get('orderby');

        switch( $orderby ){
            case 'date_event': 
                $query->set('meta_key','date_event');
                $query->set('orderby','meta_value_datetime');
                $query->set('meta_type', 'datetime');
                break;
            default: break;
        }
    }

    /**
     * Exportation de deux fichiers JSON
     */
    public function json_carto_export() {
        if(isset($_GET['generate_json'])) {
            
            $arg = array(
                'post_type' => 'evenement',
                'post_status' => 'publish',
                'posts_per_page' => -1,
            );

            global $post;
            
            $arr_post = get_posts($arg);
            
            foreach ($arr_post as $post) {
                setup_postdata($post);
                
                $arr_post_meta = get_post_meta(get_the_ID(), '_carto_event_key', true);

                $id = get_the_id();
                
                $lat = number_format((float)$arr_post_meta['coords']['lat'], 6, '.', '');
                
                $long = number_format((float)$arr_post_meta['coords']['long'], 6, '.', '');
                
                //$approved = $arr_post_meta['approved'];

                $public = $post->post_status;
                
                $title = get_the_title();
                
                $ref_gf = $arr_post_meta['ref_gf'];
                
                $date_event = $arr_post_meta['date_event'];
                
                $date_du_jour = date('Y-m-d');
                
                setlocale(LC_TIME, "fr_FR.utf-8", "French");
                $the_date = date('Y-m-d',strtotime($date_event));
                $new_date = strftime("%e %B %Y", strtotime($the_date));

                $heure = date('H:i',strtotime($date_event));
                
                $type_event = $arr_post_meta['type_event'];
                
                $delegation = $arr_post_meta['dept'];
                
                $url_calendar = $this->plugin_url . 'assets/icon/schedule.png';
                
                $url_type_event = $this->plugin_url . 'assets/icon/megaphone.png';
                
                $url_icon_gf = $this->plugin_url . 'assets/icon/icon_gf.png';

                /* Vérification et boucle sur Référendum */
                if($public == 'publish' && $ref_gf === 'ref' && $date_event > $date_du_jour){
                    $features_ref[] = array(
                        'type' => 'Feature',
                        'id' => $id,
                        'properties' => array(
                                'popupContent' => '<div class="title">'.$title.'</div><div class="date"><img class="icon" src="'.$url_calendar.'" />'.$new_date.' à '.$heure.'</div><div class="type"><img class="icon" src="'.$url_type_event.'" />'.$type_event.'</div>'
                            ),
                        'geometry' => array(
                            'type' => 'Point', 
                            'coordinates' => array(
                                    $long,
                                    $lat,
                            ),
                        ),
                    );
                }

                /* Vérification et boucle sur GF */
                if($public == 'publish' && $ref_gf === 'gf' && $date_event > $date_du_jour){
                    $features_gf[] = array(
                        'type' => 'Feature',
                        'id' => $id,
                        'properties' => array('popupContent' => '<div class="title">'.$title.'</div><div class="date"><img class="icon" src="'.$url_calendar.'" />'.$new_date.' à '.$heure.'</div><div class="type"><img class="icon" src="'.$url_type_event.'" />'.$type_event.'</div><div class="delegation"><img class="icon" src="'.$url_icon_gf.'" />Organisé par délégation GF '.$delegation.'</div>'),
                        'geometry' => array(
                            'type' => 'Point', 
                            'coordinates' => array(
                                    $long,
                                    $lat,
                            ),
                        ),
                    );
                }  
            }

            var_dump($public);

            $new_data_ref = array(
                'type' => 'FeatureCollection',
                
                'features' => $features_ref,
            );

            $new_data_gf = array(
                'type' => 'FeatureCollection',
                
                'features' => $features_gf,
            );

            $url_ref = $this->plugin_path . '\assets\geodata\referendum.js';
            
            $url_gf = $this->plugin_path . '\assets\geodata\generation.js';

            $data_encode_ref = json_encode($new_data_ref);
            
            $data_encode_gf = json_encode($new_data_gf);

            file_put_contents($url_ref, 'var referendum = ' . $data_encode_ref . ';');
            
            file_put_contents($url_gf, 'var generation = ' . $data_encode_gf . ';');

            add_action('admin_notices', array($this,'json_export_success' ));

        }
    }

    public function json_export_success() {
        ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e( 'Export des données sur la carte réussi !', 'sample-text-domain' ); ?></p>
            </div>
        <?php
    }

    /**
     * Création d'un nouveau type de contenu événement
     */
    public function event_cpt() 
    {
        $labels = array(
            'name' => 'Événements',
            'singular_name' => 'Événement'
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => false,
            'menu_icon' => 'dashicons-location',
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'supports' => array('title', 'editor')
        );

        register_post_type('evenement', $args);
    }

    /**
     * Ajout d'une MetaBoxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'event_author',
            'Evenement',
            array($this, 'render_features_box'),
            'evenement',
            'side',
            'default'
        );
    }

    /**
     * Rendu de la Metabox (Admin)
     */
    public function render_features_box($post) {
        wp_nonce_field('carto_event', 'carto_event_nonce');

        $data = get_post_meta($post->ID, '_carto_event_key', true);
        
        //$approved = isset($data['approved']) ? $data['approved'] : false;
        
        $adresse = isset($data['adresse']) ? $data['adresse'] : '';
        
        $coords = isset($data['coords']) ? $data['coords'] : '';
        
        $date_event = isset($data['date_event']) ? $data['date_event'] : '';
        
        $dept = isset($data['dept']) ? $data['dept'] : '';
        
        $type_event = isset($data['type_event']) ? $data['type_event'] : '';
        
        $ref_gf = isset($data['ref_gf']) ? $data['ref_gf'] : '';

        ?>

        <p><?php echo post_author_meta_box( $post->ID ); ?></p>
        
        <p>
			<label for="carto_event_date_event">Date de l'événement</label>
			<input type="datetime-local" id="carto_event_date_event" name="carto_event_date_event" class="widefat" value="<?php echo esc_attr( $date_event ); ?>">
		</p>
		
        <!-- <p>
			<label for="carto_event_approved">Validation</label>
			<input type="checkbox" id="carto_event_approved" name="carto_event_approved" value="1" <?php echo $approved ? 'checked' : ''; ?>>
			<label for="carto_event_approved"><div></div></label>	
		</p> -->
        
        <p>
			<div>Type d'événement : </div>
			<input type="radio" id="carto_event_type_event_1" name="carto_event_type_event" value="Réunion publique" <?php echo ($type_event === 'Réunion publique') ? 'checked' : ''; ?>>
			<label for="carto_event_type_event_1"><?php esc_attr_e( 'Réunion publique', 'carto_event' ); ?></label><br />
            <input type="radio" id="carto_event_type_event_2" name="carto_event_type_event" value="Table d'information" <?php echo ($type_event === 'Table d\'information') ? 'checked' : ''; ?>>
			<label for="carto_event_type_event_2"><?php esc_attr_e( 'Table d\'information', 'carto_event' ); ?></label><br />
            <input type="radio" id="carto_event_type_event_3" name="carto_event_type_event" value="Manifestation" <?php echo ($type_event === 'Manifestation') ? 'checked' : ''; ?>>
			<label for="carto_event_type_event_3"><?php esc_attr_e( 'Manifestation', 'carto_event' ); ?></label>
		</p>
        
        <p>
			<div>Référendum Frexit / Génération Frexit : </div>
			<input type="radio" id="carto_event_ref_gf_1" name="carto_event_ref_gf" value="ref" <?php echo ($ref_gf === 'ref') ? 'checked' : ''; ?>>
			<label for="carto_event_ref_gf_1"><?php esc_attr_e( 'Référendum Frexit', 'carto_event' ); ?></label><br />
            <input type="radio" id="carto_event_ref_gf_2" name="carto_event_ref_gf" value="gf" <?php echo ($ref_gf === 'gf') ? 'checked' : ''; ?>>
            <label for="carto_event_ref_gf_2"><?php esc_attr_e( 'Génération Frexit', 'carto_event' ); ?></label><br />
        </p>

        <p>
            <label for="carto_event_adresse">Adresse</label>
            <textarea id="carto_event_adresse" style="width: 250px;" name="carto_event_adresse" placeholder="Adresse postale précise"><?php echo esc_attr($adresse); ?></textarea>
            <em>Ex: 32 Rue du Moulin Dannemois</em>
        </p>
        
        <p>Département : <?php echo esc_attr($dept); ?> </p>
        
        <p>Coordonnées GPS (Générées) :
            <ul>
                <li>
                    <em>Lat : <?php echo (isset($coords['lat'])) ? esc_attr($coords['lat']) : 'Latitude'; ?></em>
                </li>
                <li>
                    <em>Long : <?php echo (isset($coords['long'])) ? esc_attr($coords['long']) : 'Longitude'; ?></em>
                </li>
            </ul>  
        </p>

        <?php
    }

    /**
     * Enregistrement des données de la metabox
     */
    public function save_meta_box($post_id){

        if( isset( $_POST['carto_event_adresse'] ) ) {
            $adresse = wp_strip_all_tags( $_POST[ 'carto_event_adresse' ] );
            
            $coords = $this->get_lat_lng( $adresse );
            
            $code_postal = $this->get_postcode($adresse);
            
            $dept = preg_replace('#([0-9]{2})( )?([0-9]{3})#', '$1', $code_postal);
        }

        if(!isset($_POST['carto_event_nonce'])){
            return $post_id;
        }
        
        $nonce = $_POST['carto_event_nonce'];
        
        if(!wp_verify_nonce($nonce, 'carto_event')){
            return $post_id;
        }

        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
            return $post_id;
        }

        if(!current_user_can('edit_post', $post_id)){
            return $post_id;
        }

        $data = array(
            'approved' => isset($_POST['carto_event_approved']) ? 1 : 0,
            'adresse' => sanitize_text_field($_POST['carto_event_adresse']),
            'coords' => $coords,
            'dept' => $dept,
            'date_event' => sanitize_text_field($_POST['carto_event_date_event']),
            'heure_event' => sanitize_text_field($_POST['carto_event_heure_event']),
            'type_event' => sanitize_text_field($_POST['carto_event_type_event']),
            'ref_gf' => sanitize_text_field($_POST['carto_event_ref_gf'])
        );

        if( $coords != '' ) {
            update_post_meta($post_id, '_carto_event_key', $data);
        }
    }

    /**
     * Récupérer les coordonées GPS Lat/Long
     * @return $coord
     */
    public function get_lat_lng( $address ) {
		$address = rawurlencode( $address );
		
        $coord   = get_transient( 'geocode_' . $address );

		if( empty( $coord ) ) {
			$url  = 'http://nominatim.openstreetmap.org/?format=json&addressdetails=1&q=' . $address . '&format=json&limit=1';
			
            $json = wp_remote_get( $url );
			
            if ( 200 === (int) wp_remote_retrieve_response_code( $json ) ) {
				$body = wp_remote_retrieve_body( $json );
				$json = json_decode( $body, true );
			}

			$coord['lat']  = $json[0]['lat'];
			
            $coord['long'] = $json[0]['lon'];
			
            set_transient( 'geocode_' . $address, $coord, DAY_IN_SECONDS * 90 );
		}

		return $coord;
	}

    /**
     * Récupérer le code postal (avec Nominatim)
     * @return $postcode
     */
    public function get_postcode($address) {
        $address = rawurlencode( $address );
        
        $postcode = '';

        if( empty( $coord ) ) {
			$url  = 'http://nominatim.openstreetmap.org/?format=json&addressdetails=1&q=' . $address . '&format=json&limit=1';
			$json = wp_remote_get( $url );
			if ( 200 === (int) wp_remote_retrieve_response_code( $json ) ) {
				$body = wp_remote_retrieve_body( $json );
				$json = json_decode( $body, true );
			}
            
            $postcode = $json[0]['address']['postcode'];
        }

        return $postcode;
    }

    /**
     * Création de la page shortcode
     */
    public function setShortcodePage() {
        $subpage = array(
            array(
                'parent_slug' => 'edit.php?post_type=evenement',
                'page_title' => 'Shortcodes',
                'menu_title' => 'Shortcodes',
                'capability' => 'manage_options',
                'menu_slug' => 'carto_event_shortcode',
                'callback' => array($this->callbacks, 'shortcodePage')
            )
        );

        $this->settings->addsubPages($subpage)->register();
    }

    /**
     * Affichage de la cartographie avec Leaflet (Front)
     * @return $ob_get_clean()
     */
    public function cartoevent_leaflet() {
        ob_start();       

        echo "<link rel=\"stylesheet\" href=\"https://unpkg.com/leaflet@1.7.1/dist/leaflet.css\" type=\"text/css\" media=\"all\" />";
        
        echo "<link rel=\"stylesheet\" href=\"$this->plugin_url/assets/styles/style.css\" type=\"text/css\" media=\"all\" />";
        
        require_once("$this->plugin_path/templates/leaflet.php");
        
        echo "<script src=\"https://unpkg.com/leaflet@1.7.1/dist/leaflet.js\"></script>";
        
        echo "<script src=\"$this->plugin_url/assets/geodata/results.js\"></script>";
        
        echo "<script src=\"$this->plugin_url/assets/geodata/generation.js\"></script>";
        
        echo "<script src=\"$this->plugin_url/assets/geodata/referendum.js\"></script>";
        
        echo "<script src=\"$this->plugin_url/assets/map-leaflet.js\"></script>";

        return ob_get_clean();
    }

    /**
     * Affichage du formulaire de soumission d'événement (Front)
     * @return $ob_get_clean()
     */
    public function evenement_form() {
        ob_start();

        echo "<link rel=\"stylesheet\" href=\"$this->plugin_url/assets/styles/style.css\" type=\"text/css\" media=\"all\" />";
        
        require_once("$this->plugin_path/templates/event_form.php");
        
        echo "<script src=\"$this->plugin_url/assets/form.js\"></script>";        
        
        return ob_get_clean();
    }

    /**
     * Soumission du formulaire (Front)
     * @return $this->return_json('error');
     */
    public function submit_cartoevent(){
        if(!DOING_AJAX || !check_ajax_referer('cartoevent-nonce', 'nonce')){
            return $this->return_json('error');
        }

        // Nettoie les données
        $title = sanitize_text_field($_POST['title']);
        $description = sanitize_textarea_field($_POST['description']);
        $adresse = sanitize_textarea_field($_POST['adresse']);
        $type_event = sanitize_text_field($_POST['type_event']);
        $date_event = sanitize_text_field($_POST['date_event']);
        $heure_event = sanitize_text_field($_POST['heure_event']);
        $ref_gf = sanitize_textarea_field($_POST['ref_gf']);
        
        // Vérification de l'adresse et récupération des coordonnées GPS et Département
        if( isset( $adresse ) ) {
            $adresse = wp_strip_all_tags( $adresse  );
            $coords = $this->get_lat_lng( $adresse );
            $code_postal = $this->get_postcode($adresse);
            $dept = preg_replace('#([0-9]{2})( )?([0-9]{3})#', '$1', $code_postal);
        }

        // Enregistre les données
        $data = array(
            'title' => $title,
            'approved' => 0,
            'adresse' => $adresse,
            'coords' =>$coords,
            'dept' =>$dept,
            'type_event' =>$type_event,
            'date_event' => $date_event,
            'heure_event' => $heure_event,
            'ref_gf' => $ref_gf
        );

        $args = array(
            'post_title' => $title,
            'post_content' => $description,
            'post_author' => 1,
            'post_status' => 'draft',
            'post_type' => 'evenement',
            'meta_input' => array(
                '_carto_event_key' => $data
            )
        );

        // Envoie et sauvegarde la réponse
        $postID = wp_insert_post($args);

        if($postID) {
            return $this->return_json('success');
        }

        return $this->return_json('error');
    }

    /**
     * Renvoie un json pour le formulaire de soumission (Front)
     */
    public function return_json($status) {
        $return = array(
            'status' => $status
        );
        wp_send_json($return);

        wp_die();
    }
}