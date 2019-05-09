<?php
/**
 * Created by PhpStorm.
 * User: wangxin
 * Date: 2019-04-29
 * Time: 17:32
 */
namespace App\Observers;
use App\GeneralOffer;
use App\Observers\UpdateIndex;
use Illuminate\Support\Facades\Log;

class GeneralOfferObserver {
    use UpdateIndex;
    protected $offer;
    public function __construct(GeneralOffer $offer)
    {
        $this->offer = $offer;
    }

    public function created(GeneralOffer $offer)
    {
        $this->calls($offer);
    }
    public function saved(GeneralOffer $offer)
    {
        $this->calls($offer);
    }
    public function updated(GeneralOffer $offer)
    {
        $this->calls($offer);
    }
    public function deleted(GeneralOffer $offer)
    {
        $this->calls($offer);
    }
    public function calls(GeneralOffer $offer)
    {
        $data = ["brand_price" => 0];
        $offers = $this->offer->where(["serviceor_id" => $offer->serviceor_id])->count();
        $data["brand_price"] = $offers;
        Log::info("brand_price:".$offers);
        $this->rewrite($data, $offer->serviceor_id);
    }
}