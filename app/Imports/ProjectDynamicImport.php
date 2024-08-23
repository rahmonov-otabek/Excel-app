<?php

namespace App\Imports;

use App\Models\Task;
use App\Models\Type;
use App\Models\Payment;
use App\Models\Project;
use App\Models\FailedRow;
use App\Factory\ProjectFactory;
use Illuminate\Support\Collection;
use App\Factory\ProjectDynamicFactory;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ProjectDynamicImport implements ToCollection, WithValidation, SkipsOnFailure, WithStartRow, WithEvents
{
    use RegistersEventListeners;
    
    private Task $task;
    private static array $headings;

    const STATIC_ROW = 12;

    public function __construct($task)
    {
        $this->task = $task;
    }



    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {

        $typesMap = $this->getTypesMap(Type::all()); 

        
        foreach($collection as $row){  
            if(!isset($row[1])) continue;
             
            $map = $this->getRowsMap($row);
            $projectFactory = ProjectDynamicFactory::make($typesMap, $map['static']);

            $project = Project::updateOrCreate([
                'type_id' => $projectFactory->getValues()['type_id'],
                'title' => $projectFactory->getValues()['title'],
                'created_at_time' => $projectFactory->getValues()['created_at_time'],
                'contracted_at' => $projectFactory->getValues()['contracted_at'],
            ], $projectFactory->getValues());

            if(!isset($map['dynamic'])) continue;

            $dynamicHeadings = $this->getRowsMap(self::$headings)['dynamic'];
            
            foreach($map['dynamic'] as $key => $item){
                Payment::create([
                    'project_id' => $project->id,
                    'title' => $dynamicHeadings[$key],
                    'value' => $item,
                ]);
            }

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

    private function getRowsMap($row)
    {
        $static = [];
        $dynamic = [];

        foreach($row as $key => $value){
            if($value){
                $key > 12 ? $dynamic[$key] = $value : $static[$key] = $value;
            }
        }

        return [
            'static' => $static,
            'dynamic' => $dynamic,
        ];
    }

    public function rules(): array
    {
        return [
            // 'tip' => 'required|string',
            // 'naimenovanie' => 'required|string',
            // 'data_sozdaniia' => 'required|integer',
            // 'podpisanie_dogovora' => 'required|integer',
            // 'dedlain' => 'nullable|integer', 
            // 'setevik' => 'nullable|string',
            // 'nalicie_autsorsinga' => 'nullable|string',
            // 'nalicie_investorov' => 'nullable|string',
            // 'sdaca_v_srok' => 'nullable|string',
            // 'vlozenie_v_pervyi_etap' => 'nullable|integer',
            // 'vlozenie_vo_vtoroi_etap' => 'nullable|integer',
            // 'vlozenie_v_tretii_etap' => 'nullable|integer',
            // 'vlozenie_v_cetvertyi_etap' => 'nullable|integer',
            // 'kolicestvo_ucastnikov' => 'nullable|integer',
            // 'kolicestvo_uslug' => 'nullable|integer',
            // 'kommentarii' => 'nullable|string',
            // 'znacenie_effektivnosti' => 'nullable|numeric',
        ];
    }   
    
    public function onFailure(Failure ...$failures)
    {
        $map = [];
        foreach($failures as $failure){ 
            foreach($failure->errors() as $error){
                $map[] = [
                    'key' => $this->attributesMap()[$failure->attribute()],
                    'row' => $failure->row(),
                    'message' => $error,
                    'task_id' => $this->task->id,
                ];
            }
        }

        if(count($map)>0) FailedRow::insertFailedRows($map, $this->task);
    }   

    private function attributesMap(): array
    {
        return [
            'tip' => 'Тип',
            'naimenovanie' => 'Наименование',
            'data_sozdaniia' => 'Дата создания',
            'podpisanie_dogovora' => 'Подписание договора',
            'dedlain' => 'Дедлайн', 
            'setevik' => 'Сетевик',
            'nalicie_autsorsinga' => 'Наличие аутсорсинга',
            'nalicie_investorov' => 'Наличие инвесторов',
            'sdaca_v_srok' => 'Сдача в срок',
            'vlozenie_v_pervyi_etap' => 'Вложение в первый этап',
            'vlozenie_vo_vtoroi_etap' => 'Вложение во второй этап',
            'vlozenie_v_tretii_etap' => 'Вложение в третий этап',
            'vlozenie_v_cetvertyi_etap' => 'Вложение в четвертый этап',
            'kolicestvo_ucastnikov' => 'Количество участников',
            'kolicestvo_uslug' => 'Количество услуг',
            'kommentarii' => 'Комментарий',
            'znacenie_effektivnosti' => 'Значение эффективности',
        ];
    }  	 
    
    public function startRow(): int
    {
        return 2;
    }

    public static function beforeSheet(BeforeSheet $event)
    {
        self::$headings = $event->getSheet()->getDelegate()->toArray()[0];
    }
}
