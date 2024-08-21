<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\Type;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ProjectImport implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {

        $typesMap = $this->getTypesMap(Type::all()); 

        
        foreach($collection as $row){
            if(!isset($row['naimenovanie'])) continue;
            
            Project::create([ 
                'type_id' => $this->getTypeId($typesMap, $row['tip']),
                'title' => $row['naimenovanie'],
                'created_at_time'=> Date::excelToDateTimeObject($row['data_sozdaniia']),
                'contracted_at'=> Date::excelToDateTimeObject($row['podpisanie_dogovora']),
                'deadline'=> isset($row['setevik']) ? Date::excelToDateTimeObject($row['dedlain']) : null,
                'is_chain'=> isset($row['setevik']) ? $this->getBool($row['setevik']) : null,
                'is_on_time'=> isset($row['sdaca_v_srok']) ? $this->getBool($row['sdaca_v_srok']) : null,
                'has_outsource'=> isset($row['nalicie_autsorsinga']) ? $this->getBool($row['nalicie_autsorsinga']) : null,
                'has_investors'=> isset($row['nalicie_investorov']) ? $this->getBool($row['nalicie_investorov']) : null,
                'worker_count'=> $row['kolicestvo_ucastnikov'] ?? null,
                'service_count'=> $row['kolicestvo_uslug'] ?? null, 
                'payment_first_step'=> $row['vlozenie_v_pervyi_etap'] ?? null,
                'payment_second_step'=> $row['vlozenie_vo_vtoroi_etap'] ?? null,
                'payment_third_step'=> $row['vlozenie_v_tretii_etap'] ?? null,
                'payment_forth_step'=> $row['vlozenie_v_cetvertyi_etap'] ?? null, 
                'comment'=> $row['kommentarii'] ?? null,
                'effective_value' => $row['znacenie_effektivnosti'] ?? null,
            ]);
        }
    }

    private function getTypesMap($types)
    {
        $map = [];

        foreach($types as $type){
            $map[$type->title] = $type->id;
        }

        return $map;
    }

    private function getTypeId($map, $title)
    {
        return isset($map[$title]) ? $map['title'] : Type::create(['title' => $title])->id;
    }

    private function getBool($item)
    {
        return $item == 'Да';
    }
}
