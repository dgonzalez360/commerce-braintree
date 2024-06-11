<?php

namespace webdna\commerce\braintree\controllers;

use Craft;
use craft\web\Controller;

use webdna\commerce\braintree\gateways\Gateway;
use craft\commerce\Plugin as Commerce;
use yii\web\Response;

use yii\web\HttpException;
use yii\web\JsonResponseFormatter;

use Braintree;

/**
 * @author    David Casini
 * @package   Affiliate
 * @since     1.0.0
 */
class UsBankVerificationController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config = []);
        $this->enableCsrfValidation = false;
    }

	public function actionVerifyBankAccount(): Response
	{
		
		$request = Craft::$app->getRequest();
		
        $user = Craft::$app->getUser()->getIdentity();

        /*$handle = $request->getBodyParam('gateway');
		$nonce = $request->getBodyParam('nonce');*/

        $handle = $request->getParam('gateway');
		$nonce = $request->getParam('nonce');

        $gateway = Commerce::getInstance()->getGateways()->getGatewayByHandle($handle);
        $customer = $gateway->getCustomer($user);

        $response = $gateway->gateway->paymentMethod()->create([
			'customerId' => $customer != null ? $customer->id : $user->uid,
			'paymentMethodNonce' => $nonce,
            //'verificationMerchantAccountId' => 'mwcompany_b2b',
			'options' => [
				'usBankAccountVerificationMethod' => Braintree\Result\UsBankAccountVerification::NETWORK_CHECK,
                'verificationMerchantAccountId' => 'mwcompany_b2b'
			],
		]);

        $data = [
            'success' => $response->success,
            'verified' => isset($response->paymentMethod) ? $response->paymentMethod->verified : false ,
            'msg' => '',
            'data' => [
                'token' => isset($response->paymentMethod) ? $response->paymentMethod->token : false,
                'id' => isset($response->paymentMethod) ? $response->paymentMethod->globalId : false
            ]
        ];

        return $this->sendResponse($data, ($data['success'] ? 200 : 400));

	}

    public function actionListBankAccounts(): Response
	{
		
		$request = Craft::$app->getRequest();
		
        $user = Craft::$app->getUser()->getIdentity();

        $gateway = Commerce::getInstance()->getGateways()->getGatewayByHandle('braintree');
        $customer = $gateway->getCustomer($user);

        $braintreeCustomer = $gateway->gateway->customer()->find($customer->id);
        $paymentMethods = $customer->paymentMethods;
        $accounts = [];
        if($paymentMethods){
            foreach ($paymentMethods as $paymentMethod ) { 
                if(get_class($paymentMethod) == 'Braintree\UsBankAccount'){
                    $accounts[] = [
                        'account' => $paymentMethod->last4,
                        'accountType' => $paymentMethod->accountType,
                        'bankName' => $paymentMethod->bankName,
                        'token' => $paymentMethod->token
                    ];
                }
            }
        }
    
        $data = [
            'success' => $response->success,
            'verified' => isset($response->paymentMethod) ? $response->paymentMethod->verified : false ,
            'msg' => '',
            'data' => [
                'token' => isset($response->paymentMethod) ? $response->paymentMethod->token : false,
                'id' => isset($response->paymentMethod) ? $response->paymentMethod->globalId : false
            ]
        ];

        return $this->sendResponse($data, ($data['success'] ? 200 : 400));

	}

    public function sendResponse(array $data, int $httpCode = 200){
        $this->response->data = $data;

        $formatter = new JsonResponseFormatter([
            'contentType' => 'application/feed+json',
            'useJsonp' => false,
            'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            'prettyPrint' => true,
        ]);

        $formatter->format($this->response);
        $this->response->data = null;
        $this->response->format = Response::FORMAT_RAW;
        $this->response->setStatusCode(200);
        return $this->response;
    }

}