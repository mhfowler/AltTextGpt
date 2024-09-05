<?php namespace ProcessWire;

/**
 * AltTextGpt Module for ProcessWire 3.x
 *
 * A module for using ChatGpt to generate alt text for any images in your ProcessWire site which are 
 * currently missing alt text. 
 *
 * @property string $apiKey Open AI API Key
 *
 */

class AltTextGpt extends Process implements ConfigurableModule {

	/**
	 * Construct
	 * 
	 * Here we set defaults for any configuration settings
	 * 
	 */
	public function __construct() {
		parent::__construct(); // remember to call the parent
	}
	
	/**
	 * This is an optional initialization function called before any execute functions.
	 *
	 * If you don't need to do any initialization common to every execution of this module,
	 * you can simply remove this init() method. 
	 *
	 */
	public function init() {
		parent::init(); // always remember to call the parent init
	}

	/**
	 * This method is executed when a page with your Process assigned is accessed. 
 	 *
	 * This can be seen as your main or index function. You'll probably want to replace
	 * everything in this function. 
 	 *
	 * Return value is typically direct HTML markup. But it can also be an associative 
	 * array of variables to pass to a view file named either 'AltTextGpt.view.php'
	 * or 'views/execute.php' (demonstrated here). 
	 * 
	 * @return string|array
	 *
	 */
	public function ___execute() {
		
		// main page
		return $this->___executeForm();
	}	

	/**
	 * Handles the ./form/ URL
	 * 
	 * This is the page from which alt text can be generated.
	 * 
	 * @return string
	 * 
	 */
	public function ___executeForm() {
		
		$modules = $this->wire()->modules;
		$input = $this->wire()->input;
		
		$this->headline($this->_('AltTextGPT')); // the <h1> headline
		$this->browserTitle($this->_('AltTextGPT')); // The <title> tag

		/** @var InputfieldForm $form */
		$form = $modules->get('InputfieldForm');
		$form->description = $this->_('Enter the prompt, model and configurations for alt text generation.');

	    /** @var InputfieldText $field */
		$field = $modules->get('InputfieldText');
		$field->attr('name', 'api_key');
		$field->label = $this->_('Open AI API Key (can be found from your account on the Open AI website)');
		$field->icon = 'file-text-o';
		$field->value = $this->ApiKey;
		$field->required = true;
		$form->add($field);

            /** @var InputfieldText $field */
		$field = $modules->get('InputfieldText');
		$field->attr('name', 'base_url');
		$field->label = $this->_('Base URL (the urls sent to Open AI need to be publicly accessible via the internet)');
		$field->icon = 'file-text-o';
		$field->value = $this->BaseUrl ? $this->BaseUrl : 'https://' . $this->wire('config')->httpHost;
		$field->required = true;
		$form->add($field);


		/** @var InputfieldText $field */
		$field = $modules->get('InputfieldText');
		$field->attr('name', 'prompt');
		$field->label = $this->_('Text to use as prompt for generating alt text with ChatGPT (e.g. "generate alt text for the following image in one sentence")');
		$field->icon = 'file-text-o';
		$field->value = 'Generate alt text for the following image in one sentence';
		$field->required = true;
		$form->add($field);

		/** @var InputfieldText $field */
		$field = $modules->get('InputfieldText');
		$field->attr('name', 'model');
		$field->label = $this->_('Identifier of model to use (e.g. gpt-4o; a full list of available models and their identifiers can be found on the Open AI website)');
		$field->icon = 'file-text-o';
		$field->value = 'gpt-4o';
		$field->required = true;
		$form->add($field);

		/** @var InputfieldText $field */
		$field = $modules->get('InputfieldInteger');
		$field->attr('name', 'max_images');
		$field->label = $this->_('Maximum number of images to generate alt text for in one batch (e.g. 10; 0 can be entered for no limit)');
		$field->icon = 'file-text-o';
		$field->required = true;
		$field->defaultValue = 10;
		$form->add($field);

		// Create a new InputfieldCheckbox
        $inputfield = wire('modules')->get('InputfieldCheckbox');
        $inputfield->icon = 'wrench';
        $inputfield->name = 'test_mode';
        $inputfield->label = 'Test Mode';
        $inputfield->description = 'When enabled, alt text will only be generated for images which currently have "test" as their image description.';
        $form->add($inputfield);

		/** @var InputfieldSubmit $submit */
		$submit = $modules->get('InputfieldSubmit'); 
		$submit->attr('name', 'submit_now'); 
		$submit->val($this->_('Generate Alt Text'));
		$submit->icon = 'file-text-o';
		$submit->addActionValue('exit', $this->_('Submit and exit'), 'frown-o'); // after-submit actions
		$submit->addActionValue('pages', $this->_('Submit and go to page list'), 'meh-o');
		$form->add($submit);

		// check if form has been submitted
		if($input->post($submit->name)) $this->processForm($form);

		$pages = $this->wire()->pages->find("template!=admin");
		$numPages = $pages->count();
		$numImages = 0;
		$numImagesWithoutAltText = 0;
		foreach ($pages as $page) {
            $imgs = $page->images;
            if ($imgs) {
                foreach ($imgs as $img) {
                    $numImages += 1;
                    if (! $img->description) {

                        $numImagesWithoutAltText += 1;
                    }
                }
            }
        }

        $pretext = sprintf(
            "<table class='alt-text-info'>
                <tbody>
                    <tr><td>Number of pages in site:</td><td>%d</td></tr>
                    <tr><td>Number of images in site:</td><td>%d</td></tr>
                    <tr><td>Number of images without alt text:</td><td>%d</td></tr>
                </tbody>
            </table>
            <br></br>
            <div class='gpt-explanation'>
                <p>
                    This ProcessWire module, AltTextGPT, is an interface for generating alt text for
                    all of the images in your website, using the ChatGPT Open AI API.
                </p>
                <p>
                    Using the API requires an account with the Open AI API and costs money, although its
                    pay-what-you-use and the charges are minimal. For example, alt text was generated for 200 images,
                    using 94 cents of Open AI Credits. You can create an account with Open AI,
                    from <a href='https://openai.com/'>this link</a>, and then once you have an API key,
                    you can enter it below, or configure it as a permanent setting for this module via Modules->Configure->AltTextGpt.
                </p>
                <p>
                    After configuring the API key as described above, you can then use the form below
                    to generate alt text for images in the site. The module will attempt to generate alt txt
                    for every image that currently has no alt text, one at a time. Generating alt text takes a few seconds
                    for each image, so this is not an instantaneous process. For this reason, if you have many images,
                    we suggest generating alt text for the images in batches. You can also set a batch size below,
                    generating alt text for 10 or 20 images at a time, and then repeating the process, until
                    you have generated alt text for all of the images in the site. After each run,
                    the table above should show that there are fewer images without alt text in the site,
                    until eventually the table indicates that there are 0 images in the site without alt text.
                </p>
                <p>
                    Note, for alt text to show up for images uploaded in the body of a CKEditor field,
                    this configuration must be set for that field as described in <a href='https://processwire.com/talk/topic/25641-how-do-i-fill-body-field-image-alt/?do=findComment&comment=214548'>this comment</a>.
                </p>
            </div>
            <br></br>
            ",
            $numPages, $numImages, $numImagesWithoutAltText);
		$text = $pretext . $form->render();
		return $text;
	}

	// Function to generate alt text using OpenAI's API
    protected function generateAltText($apiKey, $model, $prompt, $imageUrl) {
         // Initialize WireHttp
        $http = new WireHttp();

        // Prepare the payload
        $fullPrompt = $prompt . $imageUrl;

        $payload = [
            'model' => $model,
            'messages' => [
              [
                "role" => "user",
                "content"=> [
                    [
                    "type"=> "text",
                    "text"=> $prompt
                    ],
                    [
                    "type" => "image_url",
                    "image_url" => [
                       "url"=> $imageUrl
                        ]
                    ]
                ],
              ]
            ],
            'max_tokens' => 50
        ];

        // API request headers
        $http->setHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ]);

        // set data
        $jsonData = json_encode($payload);
        $http->setData($jsonData);

        // Send POST request
        $url = "https://api.openai.com/v1/chat/completions";
        $response = $http->post($url);

        if($response !== false) {
            $responseData = json_decode($response, true);
            if (isset($responseData['choices'][0]['message']['content'])) {
                return trim($responseData['choices'][0]['message']['content']);
            } else {
                // Failed to generate alt text
                throw new \Exception($responseData);
            }
        }
        else {
            $httpCode = $http->getHttpCode();
             // Handle request exception
            $msg = 'Request failed ' . $httpCode . " :" . $http->getError();
            $this->message($msg);
            throw new \Exception($msg);
        }
    }

	/**
	 * Process an Inputfields form and respond to requested submit action
	 * 
	 * @param InputfieldForm $form
	 * 
	 */
	protected function processForm(InputfieldForm $form) {
	
		$input = $this->wire()->input;
		$session = $this->wire()->session;
		$config = $this->wire()->config;
	
		// process the form
		$form->processInput($input->post);
	
		// return now if form had errors
		if(count($form->getErrors())) return;

		// get domain used for site

        // Generate alt text for the image
        $apiKey = $form->getChildByName('api_key')->val();
        $baseUrl = $form->getChildByName('base_url')->val();
        $prompt = $form->getChildByName('prompt')->val();
        $prompt = $form->getChildByName('prompt')->val();
        $model = $form->getChildByName('model')->val();
        $max_images = $form->getChildByName('max_images')->val();
        $test_mode = $form->getChildByName('test_mode')->val();
        $this->message("Using API Key: " . $apiKey);
        $this->message("Using Open AI model: " . $model);
        $this->message("Using prompt: " . $prompt);
        $this->message("Max number of images: " . $max_images);
        if ($test_mode) {
             $this->message("Test mode is enabled.");
        }
        $this->message("Using base url: " . $baseUrl);

		// try to add to alt text to all images
		$pages = $this->wire()->pages->find("template!=admin");
		$numPages = $pages->count();
		$numImages = 0;
		$numImagesWithoutAltText = 0;
		$maxCount = $max_images;
		foreach ($pages as $page) {
             if ($numImagesWithoutAltText > $maxCount) {
                break;
            }
            $this->message("Searching page for images: " . $page->url);
            $imgs = $page->images;
            if ($imgs) {
                foreach ($imgs as $img) {
                    $numImages += 1;
                    if ((!$test_mode && !$img->description) || ($test_mode && $img->description == "test")) {
                        $numImagesWithoutAltText += 1;
                        $imageUrl = $baseUrl . $img->url;
                        $this->message("image url: " . $imageUrl);
                        try {
                            $altText = $this->generateAltText($apiKey, $model, $prompt, $imageUrl);
                            $st = var_export($altText, true);
                            $this->message("Generated new alt text: " . $st);
                            $img->description = $altText;
                        }
                        catch (\Exception $e) {
                             $this->message("Failed to generate alt text for url: " . $imageUrl);
                        }
                        if ($numImagesWithoutAltText > $maxCount) {
                            break;
                        }
                    }
                    else {
                        $this->message("Found previously set alt text: " . $img->description);
                    }
                }
            }
            $page->save();
        }
	
		/** @var InputfieldSubmit $submit */
		$submit = $form->getChildByName('submit_now');

		// user selected: submit and exit
		if($submit->submitValue === 'exit') $session->redirect('../');

		// user selected: submit and go to page list
		if($submit->submitValue === 'pages') $session->redirect($config->urls->admin);
	}


	/**
	 * Called only when your module is installed
	 *
	 * If you don't need anything here, you can simply remove this method. 
	 *
	 */
	public function ___install() {
		parent::___install(); // Process modules must call parent method
	}

	/**
	 * Called only when your module is uninstalled
	 *
	 * This should return the site to the same state it was in before the module was installed. 
	 *
	 * If you don't need anything here, you can simply remove this method. 
	 *
	 */
	public function ___uninstall() {
		parent::___uninstall(); // Process modules must call parent method
	}

	/**
	 * Get module configuration inputs
	 * 
	 * As an alternative, configuration can also be specified in an external file 
	 * with a PHP array. See an example in the /extras/AltTextGpt.config.php file. 
	 * 
	 * @param InputfieldWrapper $inputfields
	 * 
	 */
	public function getModuleConfigInputfields(array $data) {
	    $inputfields = new InputfieldWrapper();
		$modules = $this->wire()->modules;
	
		/** @var InputfieldText $f */
		$f = $modules->get('InputfieldText');
		$f->attr('name', 'ApiKey');
		$f->label = $this->_('Open AI API Key');
		$f->description = $this->_('You can get this Key from the Open AI website after making an account');
		$f->attr('value', isset($data['ApiKey']) ? $data['ApiKey'] : $this->apiKey);
		$inputfields->add($f);
	
		/** @var InputfieldText $f */
		$f = $modules->get('InputfieldText');
		$f->attr('name', 'BaseUrl');
		$f->label = $this->_('Base URL');
		$f->description = $this->_('Base URL For Fetching Images');
		$f->attr('value', isset($data['BaseUrl']) ? $data['BaseUrl'] : $this->baseUrl);
		$inputfields->add($f);

        return $inputfields;

	}
	
}

