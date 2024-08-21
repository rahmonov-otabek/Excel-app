<?php

namespace App\Imports;

use App\Models\Type;
use App\Models\Project;
use App\Factory\ProjectFactory;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

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
            
            $projectFactory = ProjectFactory::make($typesMap, $row);

            Project::updateOrCreate([
                'type_id' => $projectFactory->getValues()['type_id'],
                'title' => $projectFactory->getValues()['title'],
                'created_at_time' => $projectFactory->getValues()['created_at_time'],
                'contracted_at' => $projectFactory->getValues()['contracted_at'],
            ], $projectFactory->getValues());
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

                                                                

}
