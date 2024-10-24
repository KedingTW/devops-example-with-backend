<?php

namespace App\Repositories\Common;

abstract class CommonRepository
{
    protected $model;

    public function __construct($model = null)
    {
        $this->model = $model;
    }

    // list

    /*
      @param   array    $data   要搜尋資料
      @param   string   $sortCol 要排序的欄位名稱
      @param   string   $sort 排序規則ASC、DES
      @param   array    $like like搜尋條件
      @return  array
    */
    public function getByCondition(array $data, $sortCol = null, $sort = null, array $like = [])
    {
        return $this->model
            ->when($data, function ($query, $data) {
                foreach ($data as $key => $val) {
                    $query->where($key, $val);
                }
            })
            ->when($sortCol, function ($query) use ($sortCol, $sort) {
                $query->orderBy($sortCol, $sort);
            })
            ->when($like, function ($query, $like) {
                foreach ($like as $likeKey => $likeVal) {
                    if ($likeKey == 'check_equal') {
                        foreach ($like['check_equal'] as $equalKey => $equalVal) {
                            $query->where($equalKey, $equalVal);
                        }
                    } else {
                        $query->where($likeKey, 'like', '%' . $likeVal . '%');
                    }
                }
            });
    }

    public function getConditionFirst(array $data, $sortCol = null, $sort = null, array $like = [])
    {
        return $this->getByCondition($data, $sortCol, $sort, $like)
            ->first();
    }

    public function getConditionCommonRepository(array $data, $sortCol = null, $sort = null, array $like = [])
    {
        $offset = $data['offset'];
        $count = $data['count'];
        unset($data['offset']);
        unset($data['count']);

        return $this->getByCondition($data, $sortCol, $sort, $like)
            ->offset($offset)
            ->limit($count);
    }

    // hsuan, 20231226
    public function getListCommonRepository(array $data, $select = "*")
    {
        return $this->getByCondition($data)
                ->select($select)
                ->get()
                ->toArray();
    }

    public function getCount($data, $sortCol = null, $sort = null, array $like = [])
    {
        return $this->getByCondition($data, $sortCol, $sort, $like)->count();
    }

    public function getList(array $data, array $search)
    {
        return $this->getByCondition([], $data['sort_field'], $data['sort_order'], $search)
            ->when(@$data['is_output'] == 0, function ($query) use ($data) {
                // 若非匯出檔案，查詢的資料會受 count 限制
                $query->offset($data['offset'])
                    ->limit($data['count']);
            })
            ->get()
            ->toArray();
    }

    public function getListWithLanguage(array $data, array $search)
    {
        return $this->getByCondition([], $data['sort_field'], $data['sort_order'], $search)
            ->with(['language'])
            ->offset($data['offset'])
            ->limit($data['count'])
            ->get()
            ->toArray();
    }

    public function getExportList(array $data, array $search)
    {
        return $this->getByCondition([], $data['sort_field'], $data['sort_order'], $search)
            ->get()
            ->toArray();
    }

    public function getListCount(array $like)
    {
        return $this->getByCondition([],  '', '', $like)->count();
    }

    public function getAll()
    {
        return $this->model->all()->toArray();
    }

    /**
     * 取得 父關聯id下的所有多語系 (例:某個產線項目下 產線製程 的所有多語系)
     *
     * @param string $fatherColumn 父關聯欄位名稱
     * @param int $fatherId 父關聯id
     * @param int $languageId 排除搜尋的語系id
     */
    public function getLanguageUnderFatherId(string $fatherColumn, int $fatherId, int $languageId = null)
    {
        return $this->model->with(['language' => function ($query) use ($languageId) {
            $query->where('id', '<>', $languageId);
        }])
            ->where($fatherColumn, $fatherId)
            ->get()
            ->pluck('language')
            ->toArray();
    }

    /**
     * 取得 Table Name
     */
    public function getTableName()
    {
        return $this->model->getTable();
    }

    /**
     * 取得 Table 內的欄位名稱
     */
    public function getFillable()
    {
        return $this->model->getFillable();
    }

    public function getBetween($date)
    {
        return $this->model->whereBetween('company_setup_date', $date)->get()->toArray();
    }

    // 修改語系用,排除id等於自己
    public function checkLanguage(int $id = null, array $data)
    {
        return $this->getByCondition($data)
            ->where('id', '<>', $id)
            ->get()
            ->toArray();
    }

    // careta

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function firstOrCreate(array $data)
    {
        return $this->model->firstOrCreate($data);
    }

    // update

    public function updateById(int $id, array $data)
    {
        return $this->model->where('id', $id)->update($data);
    }

    // delete

    public function deleteById(int $id)
    {
        return $this->model->where('id', $id)->delete();
    }

    public function deleteByIds(array $ids)
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function delete()
    {
        return $this->model->delete();
    }

    public function truncate()
    {
        return $this->model->truncate();
    }
}
