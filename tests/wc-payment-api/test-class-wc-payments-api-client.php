<?php
/**
 * Class WC_Payments_API_Client_Test
 *
 * @package WooCommerce\Payments\Tests
 */

/**
 * WC_Payments_API_Client unit tests.
 */
class WC_Payments_API_Client_Test extends WP_UnitTestCase {

	/**
	 * System under test
	 *
	 * @var WC_Payments_API_Client
	 */
	private $payments_api_client;

	/**
	 * Mock HTTP client.
	 *
	 * @var WC_Payments_Http|PHPUnit_Framework_MockObject_MockObject
	 */
	private $mock_http_client;

	/**
	 * Pre-test setup
	 */
	public function setUp() {
		parent::setUp();

		$this->mock_http_client = $this->getMockBuilder( 'WC_Payments_Http' )
			->disableOriginalConstructor()
			->setMethods( array( 'remote_request' ) )
			->getMock();

		$this->payments_api_client = new WC_Payments_API_Client(
			'Unit Test Agent/0.1.0',
			$this->mock_http_client
		);

		$this->payments_api_client->set_account_id( 'test_acc_id_12345' );
	}

	/**
	 * Test a successful call to create_charge.
	 *
	 * @throws Exception - In the event of test failure.
	 */
	public function test_create_charge_success() {
		// Mock up a test response from WP_Http.
		$this->mock_http_client
			->expects( $this->any() )
			->method( 'remote_request' )
			->will(
				$this->returnValue(
					array(
						'headers'  => array(),
						'body'     => wp_json_encode(
							array(
								'id'      => 'test_charge_id',
								'amount'  => 123,
								'created' => 1557224304,
								'status'  => 'success',
							)
						),
						'response' => array(
							'code' => 200,
						),
						'cookies'  => array(),
						'filename' => null,
					)
				)
			);

		// Attempt to create a charge.
		$result = $this->payments_api_client->create_charge( 123, 'test_source' );

		// Assert amount returned is correct (ignoring other properties for now since this is a stub implementation).
		$this->assertEquals( 123, $result->get_amount() );
	}

	/**
	 * Test a successful call to create_intention.
	 *
	 * @throws Exception - In the event of test failure.
	 */
	public function test_create_intention_success() {
		$expected_amount = 123;
		$expected_status = 'succeeded';

		$this->mock_http_client
			->expects( $this->any() )
			->method( 'remote_request' )
			->will(
				$this->returnValue(
					array(
						'headers'  => array(),
						'body'     => wp_json_encode(
							array(
								'id'      => 'test_intention_id',
								'amount'  => $expected_amount,
								'created' => 1557224304,
								'status'  => $expected_status,
								'charges' => [
									'total_count' => 1,
									'data'        => [
										[
											'id'      => 'test_charge_id',
											'amount'  => $expected_amount,
											'created' => 1557224305,
											'status'  => 'succeeded',
										],
									],
								],
							)
						),
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'cookies'  => array(),
						'filename' => null,
					)
				)
			);

		$result = $this->payments_api_client->create_and_confirm_intention( $expected_amount, 'usd', 'pm_123456789' );
		$this->assertEquals( $expected_amount, $result->get_amount() );
		$this->assertEquals( $expected_status, $result->get_status() );
	}

	/**
	 * Test a successful call to refund_charge.
	 *
	 * @throws Exception - In the event of test failure.
	 */
	public function test_create_refund_success() {
		$expected_amount = 123;

		// Mock up a test response from WP_Http.
		$this->mock_http_client
			->expects( $this->any() )
			->method( 'remote_request' )
			->will(
				$this->returnValue(
					array(
						'headers'  => array(),
						'body'     => wp_json_encode(
							array(
								'id'     => 'test_refund_id',
								'amount' => $expected_amount,
								'status' => 'succeeded',
							)
						),
						'response' => array(
							'code' => 200,
						),
						'cookies'  => array(),
						'filename' => null,
					)
				)
			);

		// Attempt to create a refund.
		$refund = $this->payments_api_client->refund_charge( 'test_charge_id', $expected_amount );

		// Assert amount returned is correct (ignoring other properties for now since this is a stub implementation).
		$this->assertEquals( $expected_amount, $refund['amount'] );
	}

	/**
	 * Test a successful call to create_intention with manual capture.
	 *
	 * @throws Exception - In the event of test failure.
	 */
	public function test_create_intention_authorization_success() {
		$expected_amount = 123;
		$expected_status = 'requires_capture';

		$this->mock_http_client
			->expects( $this->any() )
			->method( 'remote_request' )
			->with( $this->anything(), $this->stringContains( '"manual"' ) )
			->will(
				$this->returnValue(
					array(
						'headers'  => array(),
						'body'     => wp_json_encode(
							array(
								'id'      => 'test_intention_id',
								'amount'  => $expected_amount,
								'created' => 1557224304,
								'status'  => $expected_status,
								'charges' => [
									'total_count' => 1,
									'data'        => [
										[
											'id'      => 'test_charge_id',
											'amount'  => $expected_amount,
											'created' => 1557224305,
											'status'  => 'succeeded',
										],
									],
								],
							)
						),
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'cookies'  => array(),
						'filename' => null,
					)
				)
			);

		$result = $this->payments_api_client->create_and_confirm_intention( $expected_amount, 'usd', 'pm_123456789', true );
		$this->assertEquals( $expected_amount, $result->get_amount() );
		$this->assertEquals( $expected_status, $result->get_status() );
	}

	/**
	 * Test a successful call to capture intention.
	 *
	 * @throws Exception - In the event of test failure.
	 */
	public function test_capture_intention_success() {
		$expected_amount = 103;
		$expected_status = 'succeeded';

		$this->mock_http_client
			->expects( $this->any() )
			->method( 'remote_request' )
			->will(
				$this->returnValue(
					array(
						'headers'  => array(),
						'body'     => wp_json_encode(
							array(
								'id'              => 'test_intention_id',
								'amount'          => 123,
								'amount_captured' => $expected_amount,
								'created'         => 1557224304,
								'status'          => $expected_status,
								'charges'         => [
									'total_count' => 1,
									'data'        => [
										[
											'id'      => 'test_charge_id',
											'amount'  => $expected_amount,
											'created' => 1557224305,
											'status'  => 'succeeded',
										],
									],
								],
							)
						),
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'cookies'  => array(),
						'filename' => null,
					)
				)
			);

		$result = $this->payments_api_client->capture_intention( 'test_intention_id', $expected_amount );
		$this->assertEquals( $expected_status, $result->get_status() );
	}

	/**
	 * Test a successful call to cancel intention.
	 *
	 * @throws Exception - In the event of test failure.
	 */
	public function test_cancel_intention_success() {
		$expected_status = 'canceled';

		$this->mock_http_client
			->expects( $this->any() )
			->method( 'remote_request' )
			->will(
				$this->returnValue(
					array(
						'headers'  => array(),
						'body'     => wp_json_encode(
							array(
								'id'      => 'test_intention_id',
								'amount'  => 123,
								'created' => 1557224304,
								'status'  => $expected_status,
								'charges' => [
									'total_count' => 1,
									'data'        => [
										[
											'id'      => 'test_charge_id',
											'amount'  => 123,
											'created' => 1557224305,
											'status'  => 'succeeded',
										],
									],
								],
							)
						),
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'cookies'  => array(),
						'filename' => null,
					)
				)
			);

		$result = $this->payments_api_client->cancel_intention( 'test_intention_id' );
		$this->assertEquals( $expected_status, $result->get_status() );
	}
}
