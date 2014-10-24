<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 14/10/24
 * Time: 23:18
 */

namespace Xjtuwangke\LaravelPayments\Seeds;

use Xjtuwangke\LaravelPayments\Models\PaymentModel;
use Xjtuwangke\LaravelSeeder\BasicTableSeeder;

class PaymentsTableSeeder extends BasicTableSeeder{

    protected $tables = [ 'Xjtuwangke\LaravelPayments\Models\PaymentModel' ];

    protected $payments = array(
        'Alipay_Express' =>  [ 'title' => '支付宝即时到账接口' , 'desc' => '' , 'partner_id' => '' , 'partner_key' => '' , 'seller_email' => '' , 'ca_cert' => 'alipay/cacert.pem' ] ,
        'Alipay_Secured' =>  [ 'title' => '支付宝担保交易接口' , 'desc' => '' , 'partner_id' => '' , 'partner_key' => '' , 'seller_email' => '', 'ca_cert' => 'alipay/cacert.pem' ] ,
        'Alipay_Dual' =>  [ 'title' => '支付宝双功能交易接口' , 'desc' => '' , 'partner_id' => '' , 'partner_key' => '' , 'seller_email' => '', 'ca_cert' => 'alipay/cacert.pem' ] ,
        'Alipay_WapExpress' =>  [ 'title' => '支付宝WAP客户端接口' , 'desc' => '' , 'partner_id' => '' , 'partner_key' => '' , 'seller_email' => '', 'ca_cert' => 'alipay/cacert.pem' ] ,
        'Alipay_MobileExpress' =>  [ 'title' => '支付宝无线支付接口' , 'desc' => '' , 'partner_id' => '' , 'partner_key' => '' , 'seller_email' => '', 'ca_cert' => 'alipay/cacert.pem' ] ,
        'Alipay_Bank' =>  [ 'title' => '支付宝网银快捷接口' , 'desc' => '' , 'partner_id' => '' , 'partner_key' => '' , 'seller_email' => '', 'ca_cert' => 'alipay/cacert.pem' ] ,
    );

    protected function seeds_model_PaymentModel(){
        foreach( $this->payments as $key => $val ){
            $payment = new PaymentModel();
            $payment->name = $key;
            $payment->title = $val['title'];
            $payment->desc  = $val['desc'];
            $payment->partner_id = $val['partner_id'];
            $payment->partner_key = $val['partner_key'];
            $payment->seller_email = $val['seller_email'];
            $payment->options = array( 'ca_cert_path' => $val['ca_cert'] );
            $payment->save();
        }
        return null;
    }

}