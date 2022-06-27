<?php
/**
 * Elementor oEmbed Widget.
 *
 * Elementor widget that inserts an embbedable content into the page, from any given URL.
 *
 * @since 1.0.0
 */

namespace plugins\goo1\nimbuscloud\elementor\widgets;

class registration extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve oEmbed widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'goo1-nimbuscloud-registration';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve oEmbed widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Nimbuscloud Anmeldung', 'plugin-name' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve oEmbed widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'fa fa-cloud';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the oEmbed widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return ['andreaskasper','goo1','tanzraum'];
	}

	/**
	 * Register oEmbed widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'plugin-name' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT
			]
		);

		$this->add_control(
			'level',
			[
				'label' => __( 'Level', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => null,
				'options' => $arr,
			]
		);
		
		/*$this->add_control(
			'url_mp4',
			[
				'label' => __( 'Video URL mp4', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::URL
			]
        );
        
        $this->add_control(
			'url_webm',
			[
				'label' => __( 'Video URL webm', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::URL
			]
        );
        
        $this->add_control(
			'url_poster',
			[
				'label' => __( 'Poster URL', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::URL
			]
        );

        $this->add_control(
			'url_chapters_vtt',
			[
				'label' => __( 'Kapitel vtt', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::URL
			]
        );*/
        
        /*$this->add_control(
			'poster_local',
			[
				'label' => __( 'Ersatzvorschaubild', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::MEDIA
			]
		);*/

        

		$this->end_controls_section();

		/* ---------------------- */

		/*$this->start_controls_section(
			'members_section',
			[
				'label' => __( 'Mitgliedschaftsbeschränkung', 'plugin-name' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT
			]
		);

		$this->add_control(
			'member_level1',
			[
				'label' => __( 'Level 1: Basic', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'anzeigen', 'your-plugin' ),
				'label_off' => __( 'verstecken', 'your-plugin' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'member_level2',
			[
				'label' => __( 'Level 2: Premium', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'anzeigen', 'your-plugin' ),
				'label_off' => __( 'verstecken', 'your-plugin' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'member_level3',
			[
				'label' => __( 'Level 3: Pro', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'anzeigen', 'your-plugin' ),
				'label_off' => __( 'verstecken', 'your-plugin' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'member_referenzdatum',
			[
				'label' => __( 'Referenzdatum', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::DATE_TIME,
			]
		);


		$this->end_controls_section();*/

		

    }
    
	/**
	 * Render oEmbed widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
    protected function render() {
		$settings = $this->get_settings_for_display();
		echo('<IFRAME src="https://tanzraum.nimbuscloud.at/index.php?c=PublicCustomers&a=Courses&level='.$settings["level"].'&site=0" FRAMEBORDER="0" onload="resizeIframe(this)" style="width: 100%; border: none;"/>');
		?>
		<script>
  		function resizeIframe(obj) {
    		obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
  		}
		</script>
		<?php
	}
}