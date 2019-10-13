<?php

namespace App\Repositories;

use App\Contracts\BrandContract;
use App\Models\Brand;
use App\Traits\UploadAble;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;

class BrandRepository extends BaseRepository implements BrandContract
{
    use UploadAble;

    protected $model;

    public function __construct(Brand $model)
    {
        $this->model = $model;
    }

    public function listBrands(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        return $this->all($columns, $order, $sort);
    }

    public function findBrandById(int $id)
    {
        try {
            return $this->findOneOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException($e);
        }
    }

    public function createBrand(array $params)
    {   
        try {
            $collection = collect($params);
            $logo = null;

            if ($collection->has('logo') && $params['logo'] instanceof UploadedFile) {
                $logo = $this->uploadOne($params['logo'], 'brands');
            }

            $merge = $collection->merge(compact('logo'));
            $brand = new Brand($merge->all());
            $brand->save();
            return $brand;
        } catch (QueryException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    public function updateBrand(array $params)
    {
        $brand = $this->findBrandById($params['id']);
        try {
            $collection = collect($params);
            $logo = null;
            if ($collection->has('logo') && $params['logo'] instanceof UploadedFile) {
                
                if ($brand->logo != null) {
                    $this->deleteOne($brand->logo);
                }

                $logo = $this->uploadOne($params['logo'], 'brands');

            }

            $merge = $collection->merge(compact('logo'));

            $brand->update($merge->all());

            return $brand;
            
        } catch (QueryException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    public function deleteBrand(int $id)
    {
        $brand = $this->findBrandById($id);
        if ($brand->logo != null) {
            $this->deleteOne($brand->logo);
        }
        $brand->delete();

        return $brand;
    }
}
