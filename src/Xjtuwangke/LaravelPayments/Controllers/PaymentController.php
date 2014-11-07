<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 14/10/24
 * Time: 22:11
 */

namespace Xjtuwangke\LaravelPayments\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Xjtuwangke\LaravelPayments\Models\OrderModel;
use Xjtuwangke\LaravelPayments\Models\PaymentModel;


class PaymentController extends \Controller{

    public static function registerRoutes(){
        $class = get_class();
        \Route::get( '/pay/{id}/payto.do/{order_no}', [ 'as' => 'pay.payto' , 'uses' => "{$class}@payto" ] );
        \Route::get( '/pay/pay_form_submit.do', [ 'before' => 'csrf' , 'as' => 'pay.form_submit' , 'uses' => "{$class}@submit" ] );
        \Route::any( '/pay/{id}/return.do', [ 'as' => 'pay.return' , 'uses' => "{$class}@server_return" ] );
        \Route::post( '/pay/{id}/notify.do', [ 'as' => 'pay.notify' , 'uses' => "{$class}@notify_return" ] );

        \Route::get( '/pay/success/{order_no}' , [ 'as' => 'pay.success' , 'uses' => "{$class}@success" ] );

        \Route::get( '/pay/fail/{order_no}' , [ 'as' => 'pay.fail' , 'uses' => "{$class}@fail" ] );
    }

    public function submit(){
        $id = Input::get( 'payment_id' );
        $order_no = Input::get( 'order_no' );
        return $this->payto( $id , $order_no );
    }

    public function payto( $id ,  $order_no ){

        $payment = PaymentModel::find( $id );
        if( ! $payment ){
            \App::abort( 404 );
        }

        $order = OrderModel::where( 'order_no' , $order_no )->first();
        if( ! $order ){
            \App::abort( 404 );
        }

        $gateway = $payment->getOmniPayGateway();

        $return_url = Input::getUriForPath("/pay/{$id}/return.do");
        $notify_url = Input::getUriForPath("/pay/{$id}/notify.do");

        $gateway->setNotifyUrl($notify_url);
        $gateway->setReturnUrl($return_url);
        if( method_exists( $gateway , 'setCancelUrl' ) ){
            $cancel_url = Input::getUriForPath("/pay/{$id}/cancel.do");
            $gateway->setCancelUrl( $cancel_url );
        }

        # new order
        # db

        $order    = array(
            'out_trade_no' => $order->order_no ,
            'subject'      => Config::get( 'laravel-payments::config.site' ) . ' 订单号:' . $order_no, //order title
            'total_fee'    => $order->need_pay ,
        );
        $response = $gateway->purchase($order)->send();
        # return a payto_url, and client redirect to alipay.
        return \Redirect::to( $response->getRedirectUrl() );
    }

    public function server_return( $id ){

        $payment = PaymentModel::find( $id );
        if( ! $payment ){
            \App::abort( 404 );
        }
        $gateway = $payment->getOmniPayGateway();

        $options = $payment->options;
        if( isset( $options['ca_cert_path'] ) ){
            $options['ca_cert_path'] = Config::get( 'laravel-payments::config.cert' ) . $options['ca_cert_path'];
        }
        $options['request_params'] = Input::all();
        $request                   = $gateway->completePurchase($options)->send();
        $debug_data                = $request->getData();

        $out_trade_no = Input::get('out_trade_no');
        if ($request->isSuccessful()) { //
            #####
            # eg: $order = Order::find($out_trade_no);
            # !!!!you should check your order status here for duplicate request.
            #####
            \Event::fire( 'payment.success.return', [ 'payment' => $payment , 'order_no' => $out_trade_no , 'meta' => Input::all()] );
            return \Redirect::action( Config::get( 'laravel-payments::config.pay_success_action' ) , [ $out_trade_no ] );
            //echo 'hey! pay verify success! make a redirect with client or server here';
        } else {
            //echo 'hey! pay verify fail! make a redirect with client or server here';
            \Event::fire( 'payment.fail.return', [ 'payment' => $payment , 'order_no' => $out_trade_no , 'meta' => Input::all() , 'debug' => $debug_data ] );
            return \Redirect::action( Config::get( 'laravel-payments::config.pay_fail_action' ) , [ $out_trade_no ] );
        }
    }

    public function notify_return( $id ){

        $payment = PaymentModel::find( $id );
        if( ! $payment ){
            die( 'fail' );
        }
        $gateway = $payment->getOmniPayGateway();

        $options = $payment->options;
        if( isset( $options['ca_cert_path'] ) ){
            $options['ca_cert_path'] = Config::get( 'laravel-payments::config.cert' ) . $options['ca_cert_path'];
        }

        $options['request_params'] = Input::all();

        $request                   = $gateway->completePurchase($options)->send();
        $debug_data                = $request->getData();

        $out_trade_no = Input::get('out_trade_no');
        if ($request->isSuccessful()) {
            #####
            # eg: $order = Order::find($out_trade_no);
            # !!!!you should check your order status here for duplicate request.
            #####
            \Event::fire('payment.success.notify', [ 'payment' => $payment , 'order_no' => $out_trade_no , 'meta' => Input::all()] );
            die('success'); //it should be string 'success'
        } else {
            \Event::fire( 'payment.fail.notify', [ 'payment' => $payment , 'order_no' => $out_trade_no , 'meta' => Input::all() , 'debug' => $debug_data ] );
            die('fail'); //it should be string 'fail'
        }
    }

    public function success(){
        return \View::make( 'laravel-payments::result')->with( 'title' , '支付成功' )->with( 'content' , '<h2>您已经支付成功</h2>' );
    }

    public function fail(){
        return \View::make( 'laravel-payments::result')->with( 'title' , '支付失败' )->with( 'content' , '<h2>支付失败</h2>' );
    }


}