<?php
/**
 * The core plugin class.
 *
 * It is used to communicate with chatGPT api
 */

use Http\Discovery\Psr18Client;
use Tectalic\OpenAi\Authentication;
use Tectalic\OpenAi\Client;
use Tectalic\OpenAi\Manager;
use Tectalic\OpenAi\Models\ChatCompletions\CreateRequest;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'QCG_Connector' ) ) {

	class QCG_Connector {
		private string $apikey = 'token';
		private Client $client;

		public static array $default_models = array(
			'gpt-3.5-turbo',
			'gpt-3.5-turbo-0301',
			'text-davinci-003',
			'text-davinci-002',
			'code-davinci-002'
		);

		public function __construct( $apikey ) {
			if ( ! empty( $apikey ) ) {
				$this->apikey = $apikey;
			}

			$this->client = new Client(
				new Psr18Client(),
				new Authentication( $this->apikey ),
				Manager::BASE_URI
			);
		}

		public function chatCompetition( $ask, $settings ): string {

			try {
				$response = $this->client->chatCompletions()->create(
					new CreateRequest( [
						'model'    => $settings['model'],
						'messages' => $ask,
					] )
				)->toModel();

				return $response->choices[0]->message->content;
			} catch ( \Exception $e ) {
				return false;
			}
		}

		public function get_models(): array {
			$list     = $this->client->models()->list();
			$response = $list->getResponse();
			$list     = $list->toArray();
			$error    = false;
			$models   = array();
			if ( $response->getStatusCode() !== 200 ) {
				$error = $response->getReasonPhrase();
			} else {
				foreach ( $list['data'] as $item ) {
					$models[] = $item['id'];
				}
			}

			return array( $error, $models );
		}
	}
}
