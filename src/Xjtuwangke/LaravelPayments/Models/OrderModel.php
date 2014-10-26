<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 14/10/24
 * Time: 21:49
 */

namespace Xjtuwangke\LaravelPayments\Models;


use Illuminate\Database\Schema\Blueprint;

class OrderModel extends \BasicModel{

    protected $table = 'orders';

    public static function _schema( Blueprint $table ){
        $table = parent::_schema( $table );
        $table->string( 'order_no' , 100 )->unique(); //订单号
        $table->bigInteger( 'user_id' ); //用户id
        $table->integer( 'payment_id' )->references('id')->on('payments'); //支付方式
        $table->integer( 'need_pay' ); //应付价格
        $table->integer( 'total' ); //总价
        $table->longText( 'goods' )->nullable(); //商品详细信息
        return $table;
    }

    static public function orderNo(){
        return date('Ymdh') . sprintf( '%08d' , rand( 0 , 99999999) );
    }

    public function getNeedPayAttribute( $value ){
        return sprintf( "%.2f" , $value / 100.0 );
    }

    public function getTotalAttribute( $value ){
        return sprintf( "%.2f" , $value / 100.0 );
    }

    public function setNeedPayAttribute( $value ){
        $this->attributes[ 'need_pay' ] = round( $value * 100 );
    }

    public function setTotalAttribute( $value ){
        $this->attributes[ 'total' ] = round( $value * 100 );
    }

    public function getGoodsAttribute( $value ){
        if( $value ){
            return unserialize( $value );
        }
        else{
            return $value;
        }
    }

    public function pay( $amount ){
        $this->need_pay = $this->need_pay - $amount;
        $this->save();
    }

    public function setGoodsAttribute( $value ){
        $this->attributes['goods'] = serialize( $value );
    }

}