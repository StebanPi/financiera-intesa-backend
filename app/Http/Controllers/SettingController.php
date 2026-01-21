<?php

namespace App\Http\Controllers;
use App\Models\concepto;
use App\Models\elaborado;
use App\Models\haber;
use App\Models\debe;
use App\Models\otrosConcepto;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Group;
use App\Models\Teacher;
use App\Models\Module;
use App\Models\InstitutionSetting;
use App\Models\Entry;
use App\Models\OtherEntry;
use App\Models\EgresoReceipt;
use App\Models\ThirdReceipts;
use App\Models\Matricula;
use App\Http\Requests\ConceptoRequest;
use App\Http\Requests\ElaboradoRequest;
use App\Http\Requests\DebeRequest;
use App\Http\Requests\HaberRequest;
use App\Http\Requests\OtrosConceptosRequest;
use Illuminate\Http\Request;
use App\Models\table_change;
use App\Http\Controllers\TableChangeController;
use Illuminate\Support\Facades\DB;
use function GuzzleHttp\Promise\all;

class SettingController extends Controller
{
    //

    public function index(){
        // Obtener IDs únicos agrupados por nombre para evitar duplicados
        $conceptosIds = DB::table('conceptos')
            ->select(DB::raw('MIN(id) as id'))
            ->groupBy('nombre')
            ->pluck('id');
        $conceptos = concepto::whereIn('id', $conceptosIds)->orderBy('id')->get();
        
        $sql = concepto::where('orderTable','1')->count();
        
        // Para elaborados, agrupar por nombre
        $elaboradosIds = DB::table('elaborados')
            ->select(DB::raw('MIN(id) as id'))
            ->groupBy('nombre')
            ->pluck('id');
        $elaborado = elaborado::whereIn('id', $elaboradosIds)->orderBy('id')->get();
        
        // Para haber, agrupar por cuenta y nombre
        $habersIds = DB::table('habers')
            ->select(DB::raw('MIN(id) as id'))
            ->groupBy('cuenta', 'nombre')
            ->pluck('id');
        $haber = haber::whereIn('id', $habersIds)->orderBy('id')->get();
        
        // Para debe, agrupar por cuenta y nombre
        $debesIds = DB::table('debes')
            ->select(DB::raw('MIN(id) as id'))
            ->groupBy('cuenta', 'nombre')
            ->pluck('id');
        $debe = debe::whereIn('id', $debesIds)->orderBy('id')->get();
        
        // Para otros conceptos, agrupar por nombre
        $otrosIds = DB::table('otros_conceptos')
            ->select(DB::raw('MIN(id) as id'))
            ->groupBy('nombre')
            ->pluck('id');
        $otros = otrosConcepto::whereIn('id', $otrosIds)->orderBy('id')->get();
        
        // Los catálogos académicos nuevos no deberían tener duplicados, pero por si acaso
        $programs = Program::distinct()->orderBy('id')->get();
        $schedules = Schedule::distinct()->orderBy('id')->get();
        $groups = Group::distinct()->orderBy('id')->get();
        $teachers = Teacher::distinct()->orderBy('id')->get();
        $modules = Module::distinct()->orderBy('id')->get();
        $institucion = InstitutionSetting::getSettings();
        
        return view('setting.index',[
            'otros' => $otros,
            'conceptos' => $conceptos,
            'count' => $sql,
            'elaborados'=> $elaborado, 
            'haber' =>$haber, 
            'debe' => $debe,
            'programs' => $programs,
            'schedules' => $schedules,
            'groups' => $groups,
            'teachers' => $teachers,
            'modules' => $modules,
            'institucion' => $institucion,
        ]);
    
    
    }

    public function StoreConcepto(ConceptoRequest $request){
        // Validar que no exista otro concepto con el mismo nombre (excepto el actual si está editando)
        $query = concepto::where('nombre', $request->nombre);
        if($request->id != ""){
            $query->where('id', '!=', $request->id);
        }
        $exists = $query->exists();
        
        if($exists){
            return redirect()->route('setting.index')
                ->withErrors(['concepto_nombre' => 'Ya existe un concepto con ese nombre.'])
                ->withInput();
        }
        
        if($request->id != ""){
            $con = concepto::where('id',$request->id)->first();
            $con->nombre = $request->nombre;
            $con->estado = $request->estado;
            $con->orderTable = $request->orderTable;
            $con->consecutivo = $request->consecutivo;
            $con->save();
            TableChangeController::StoreEdit('conceptos',$con->id);
        }else{
            $sql = concepto::create($request->all());
            if($sql){
                TableChangeController::StoreAdd('conceptos',$sql->id);
            }
            
        }
        return redirect()->route('setting.index')->with('success','Guardado Correctamente');
    }
    
    public function destroyConcepto($id){
        try {
        $concepto = concepto::findOrFail($id);
        
        // Verificar si está en uso en entries
        $inUse = Entry::where('concepto', $id)->exists();
        
        if($inUse){
            return redirect()->route('setting.index')
                    ->withErrors(['error' => 'No se puede eliminar el concepto porque está siendo usado en abonos registrados.'])
                    ->with('error_message', 'No se puede eliminar el concepto "' . $concepto->nombre . '" porque está siendo usado en abonos registrados.');
        }
        
            $nombreConcepto = $concepto->nombre;
        TableChangeController::StoreDelete('conceptos', $id);
        $concepto->delete();
        
            return redirect()->route('setting.index')->with('success','Concepto "' . $nombreConcepto . '" eliminado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar concepto: ' . $e->getMessage());
            return redirect()->route('setting.index')
                ->withErrors(['error' => 'Error al eliminar el concepto: ' . $e->getMessage()])
                ->with('error_message', 'Error al eliminar el concepto: ' . $e->getMessage());
        }
    }
    public function StoreOtrosConcepto(OtrosConceptosRequest $request){
        // Validar que no exista otro concepto con el mismo nombre
        $query = otrosConcepto::where('nombre', $request->nombre);
        if($request->id != ""){
            $query->where('id', '!=', $request->id);
        }
        $exists = $query->exists();
        
        if($exists){
            return redirect()->route('setting.index')
                ->withErrors(['otros_concepto_nombre' => 'Ya existe un concepto con ese nombre.'])
                ->withInput();
        }
        
        // Validar que se proporcionen debe y haber si no es edición o si están presentes en el request
        if($request->id != ""){
            $con = otrosConcepto::where('id',$request->id)->first();
            $con->nombre = $request->nombre;
            $con->estado = $request->estado;
            if($request->has('debe')){
                $con->debe = $request->debe;
            }
            if($request->has('haber')){
                $con->haber = $request->haber;
            }
            $con->save();
            TableChangeController::StoreEdit('otros_conceptos',$con->id);
        }else{
            // Para creación, debe y haber son obligatorios
            if(!$request->has('debe') || !$request->has('haber')){
                return redirect()->route('setting.index')
                    ->withErrors(['otros_concepto_nombre' => 'Los campos Debe y Haber son obligatorios.'])
                    ->withInput();
            }
            $sql = otrosConcepto::create([
                'nombre' => $request->nombre,
                'estado' => $request->estado,
                'debe' => $request->debe,
                'haber' => $request->haber,
            ]);
            TableChangeController::StoreAdd('otros_conceptos',$sql->id);
        }
        return redirect()->route('setting.index')->with('success','Guardado Correctamente');
    }
    
    public function destroyOtrosConcepto($id){
        try {
        $otrosConcepto = otrosConcepto::findOrFail($id);
        
        // Verificar si está en uso en other_entries
        $inUse = OtherEntry::where('concepto', $id)->exists();
        
        if($inUse){
            return redirect()->route('setting.index')
                    ->withErrors(['error' => 'No se puede eliminar el concepto porque está siendo usado en otros ingresos registrados.'])
                    ->with('error_message', 'No se puede eliminar el concepto "' . $otrosConcepto->nombre . '" porque está siendo usado en otros ingresos registrados.');
        }
        
            $nombreConcepto = $otrosConcepto->nombre;
        TableChangeController::StoreDelete('otros_conceptos', $id);
        $otrosConcepto->delete();
        
            return redirect()->route('setting.index')->with('success','Concepto "' . $nombreConcepto . '" eliminado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar otro concepto: ' . $e->getMessage());
            return redirect()->route('setting.index')
                ->withErrors(['error' => 'Error al eliminar el concepto: ' . $e->getMessage()])
                ->with('error_message', 'Error al eliminar el concepto: ' . $e->getMessage());
        }
    }
    public function StoreElaborado(ElaboradoRequest $request){
        // Validar que no exista otro elaborado con el mismo nombre
        $query = elaborado::where('nombre', $request->nombre);
        if($request->id != ""){
            $query->where('id', '!=', $request->id);
        }
        $exists = $query->exists();
        
        if($exists){
            return redirect()->route('setting.index')
                ->withErrors(['elaborado_nombre' => 'Ya existe un elaborador con ese nombre.'])
                ->withInput();
        }
        
        if($request->id != ""){
            $ela = elaborado::where('id',$request->id)->first();
            $ela->nombre = $request->nombre;
            $ela->estado = $request->estado;
            $ela->save();
            TableChangeController::StoreEdit('elaborados',$ela->id);
        }else{
            $sql = elaborado::create($request->all());
            TableChangeController::StoreAdd('elaborados',$sql->id);
        }
        return redirect()->route('setting.index')->with('success','Guardado Correctamente');
    }
    
    public function destroyElaborado($id){
        try {
        $elaborado = elaborado::findOrFail($id);
        
        // Verificar si está en uso
        $inUseEntries = Entry::where('elaborado_por', $id)->exists();
        $inUseOtherEntries = OtherEntry::where('elaborado_por', $id)->exists();
        $inUseEgreso = EgresoReceipt::where('elaborado_por', $id)->exists();
        $inUseThird = ThirdReceipts::where('elaborado_por', $id)->exists();
        
        if($inUseEntries || $inUseOtherEntries || $inUseEgreso || $inUseThird){
            return redirect()->route('setting.index')
                    ->withErrors(['error' => 'No se puede eliminar el elaborador porque está siendo usado en recibos registrados.'])
                    ->with('error_message', 'No se puede eliminar el elaborador "' . $elaborado->nombre . '" porque está siendo usado en recibos registrados.');
        }
        
            $nombreElaborado = $elaborado->nombre;
        TableChangeController::StoreDelete('elaborados', $id);
        $elaborado->delete();
        
            return redirect()->route('setting.index')->with('success','Elaborador "' . $nombreElaborado . '" eliminado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar elaborado: ' . $e->getMessage());
            return redirect()->route('setting.index')
                ->withErrors(['error' => 'Error al eliminar el elaborador: ' . $e->getMessage()])
                ->with('error_message', 'Error al eliminar el elaborador: ' . $e->getMessage());
        }
    }
    public function StoreHaber(HaberRequest $request){
        // Validar que no exista otra cuenta haber con el mismo código y nombre
        $query = haber::where('cuenta', $request->cuenta)
            ->where('nombre', $request->nombre);
        if($request->id != ""){
            $query->where('id', '!=', $request->id);
        }
        $exists = $query->exists();
        
        if($exists){
            return redirect()->route('setting.index')
                ->withErrors(['haber_cuenta' => 'Ya existe una cuenta haber con ese código y nombre.'])
                ->withInput();
        }
        
        if($request->id != ""){
            $haber = haber::where('id',$request->id)->first();
            $haber->cuenta = $request->cuenta;
            $haber->nombre = $request->nombre;
            $haber->save();
            TableChangeController::StoreEdit('habers',$haber->id);
        }else{
            $sql = haber::create($request->all());
            TableChangeController::StoreAdd('habers',$sql->id);
        }
        return redirect()->route('setting.index')->with('success','Guardado Correctamente');
    }
    
    public function destroyHaber($id){
        try {
        $haber = haber::findOrFail($id);
        
        // Verificar si está en uso
        $inUseEntries = Entry::where('haber', $id)->exists();
        $inUseOtherEntries = OtherEntry::where('haber', $id)->exists();
        $inUseEgreso = EgresoReceipt::where('haber', $id)->exists();
        $inUseThird = ThirdReceipts::where('haber', $id)->exists();
        $inUseConceptos = concepto::where('haber', $id)->exists();
            $inUseOtrosConceptos = otrosConcepto::where('haber', $id)->exists();
            $inUseConceptEntry = \App\Models\ConceptEntryReceipt::where('haber', $id)->exists();
            $inUseConceptDischarge = \App\Models\ConceptDischargeReceipt::where('haber', $id)->exists();
        
            if($inUseEntries || $inUseOtherEntries || $inUseEgreso || $inUseThird || $inUseConceptos || $inUseOtrosConceptos || $inUseConceptEntry || $inUseConceptDischarge){
            return redirect()->route('setting.index')
                    ->withErrors(['error' => 'No se puede eliminar la cuenta haber porque está siendo usada en registros existentes.'])
                    ->with('error_message', 'No se puede eliminar la cuenta haber "' . $haber->cuenta . ' - ' . $haber->nombre . '" porque está siendo usada en registros existentes.');
        }
        
            $nombreHaber = $haber->cuenta . ' - ' . $haber->nombre;
        TableChangeController::StoreDelete('habers', $id);
        $haber->delete();
        
            return redirect()->route('setting.index')->with('success','Cuenta haber "' . $nombreHaber . '" eliminada correctamente');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar haber: ' . $e->getMessage());
            return redirect()->route('setting.index')
                ->withErrors(['error' => 'Error al eliminar la cuenta haber: ' . $e->getMessage()])
                ->with('error_message', 'Error al eliminar la cuenta haber: ' . $e->getMessage());
        }
    }
    public function StoreDebe(DebeRequest $request){
        // Validar que no exista otra cuenta debe con el mismo código y nombre
        $query = debe::where('cuenta', $request->cuenta)
            ->where('nombre', $request->nombre);
        if($request->id != ""){
            $query->where('id', '!=', $request->id);
        }
        $exists = $query->exists();
        
        if($exists){
            return redirect()->route('setting.index')
                ->withErrors(['debe_cuenta' => 'Ya existe una cuenta debe con ese código y nombre.'])
                ->withInput();
        }
        
        if($request->id != ""){
            $debe = debe::where('id',$request->id)->first();
            $debe->cuenta = $request->cuenta;
            $debe->nombre = $request->nombre;
            $debe->save();
            TableChangeController::StoreEdit('debes',$debe->id);
        }else{
            $sql = debe::create($request->all());
            TableChangeController::StoreAdd('debes',$sql->id);
        }
        return redirect()->route('setting.index')->with('success','Guardado Correctamente');
    }
    
    public function destroyDebe($id){
        try {
        $debe = debe::findOrFail($id);
        
        // Verificar si está en uso
        $inUseEntries = Entry::where('debe', $id)->exists();
        $inUseOtherEntries = OtherEntry::where('debe', $id)->exists();
        $inUseEgreso = EgresoReceipt::where('debe', $id)->exists();
        $inUseThird = ThirdReceipts::where('debe', $id)->exists();
        $inUseConceptos = concepto::where('debe', $id)->exists();
            $inUseOtrosConceptos = otrosConcepto::where('debe', $id)->exists();
            $inUseConceptEntry = \App\Models\ConceptEntryReceipt::where('debe', $id)->exists();
            $inUseConceptDischarge = \App\Models\ConceptDischargeReceipt::where('debe', $id)->exists();
        
            if($inUseEntries || $inUseOtherEntries || $inUseEgreso || $inUseThird || $inUseConceptos || $inUseOtrosConceptos || $inUseConceptEntry || $inUseConceptDischarge){
            return redirect()->route('setting.index')
                    ->withErrors(['error' => 'No se puede eliminar la cuenta debe porque está siendo usada en registros existentes.'])
                    ->with('error_message', 'No se puede eliminar la cuenta debe "' . $debe->cuenta . ' - ' . $debe->nombre . '" porque está siendo usada en registros existentes.');
        }
        
            $nombreDebe = $debe->cuenta . ' - ' . $debe->nombre;
        TableChangeController::StoreDelete('debes', $id);
        $debe->delete();
        
            return redirect()->route('setting.index')->with('success','Cuenta debe "' . $nombreDebe . '" eliminada correctamente');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar debe: ' . $e->getMessage());
            return redirect()->route('setting.index')
                ->withErrors(['error' => 'Error al eliminar la cuenta debe: ' . $e->getMessage()])
                ->with('error_message', 'Error al eliminar la cuenta debe: ' . $e->getMessage());
        }
    }

    // Catálogos de Gestión Académica
    public function storeProgram(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);
        
        // Validar que no exista otro programa con el mismo nombre
        $query = Program::where('name', $request->name);
        if($request->id != ""){
            $query->where('id', '!=', $request->id);
        }
        $exists = $query->exists();
        
        if($exists){
            return redirect()->route('setting.index')
                ->withErrors(['program_name' => 'Ya existe un programa con ese nombre.'])
                ->withInput();
        }
        
        if($request->id != ""){
            $program = Program::findOrFail($request->id);
            $program->update($request->only(['name', 'code', 'active']));
        }else{
            Program::create($request->only(['name', 'code', 'active']));
        }
        return redirect()->route('setting.index')->with('success','Programa guardado correctamente');
    }
    
    public function destroyProgram($id){
        try {
        $program = Program::findOrFail($id);
        
        // Verificar si está en uso en matriculas (el programa se guarda como string)
        $inUse = Matricula::where('programa', $program->name)->exists();
        
        if($inUse){
            return redirect()->route('setting.index')
                    ->withErrors(['error' => 'No se puede eliminar el programa porque está siendo usado en matrículas registradas.'])
                    ->with('error_message', 'No se puede eliminar el programa "' . $program->name . '" porque está siendo usado en matrículas registradas.');
        }
        
            $nombrePrograma = $program->name;
        $program->delete();
        
            return redirect()->route('setting.index')->with('success','Programa "' . $nombrePrograma . '" eliminado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar programa: ' . $e->getMessage());
            return redirect()->route('setting.index')
                ->withErrors(['error' => 'Error al eliminar el programa: ' . $e->getMessage()])
                ->with('error_message', 'Error al eliminar el programa: ' . $e->getMessage());
        }
    }

    public function storeSchedule(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'boolean',
        ]);
        
        // Validar que no exista otro horario con el mismo nombre
        $query = Schedule::where('name', $request->name);
        if($request->id != ""){
            $query->where('id', '!=', $request->id);
        }
        $exists = $query->exists();
        
        if($exists){
            return redirect()->route('setting.index')
                ->withErrors(['schedule_name' => 'Ya existe un horario con ese nombre.'])
                ->withInput();
        }
        
        if($request->id != ""){
            $schedule = Schedule::findOrFail($request->id);
            $schedule->update($request->only(['name', 'active']));
        }else{
            Schedule::create($request->only(['name', 'active']));
        }
        return redirect()->route('setting.index')->with('success','Horario guardado correctamente');
    }
    
    public function destroySchedule($id){
        try {
        $schedule = Schedule::findOrFail($id);
        
        // Verificar si está en uso en matriculas (el horario se guarda como string)
        $inUse = Matricula::where('horario', $schedule->name)->exists();
        
        if($inUse){
            return redirect()->route('setting.index')
                    ->withErrors(['error' => 'No se puede eliminar el horario porque está siendo usado en matrículas registradas.'])
                    ->with('error_message', 'No se puede eliminar el horario "' . $schedule->name . '" porque está siendo usado en matrículas registradas.');
        }
        
            $nombreSchedule = $schedule->name;
        $schedule->delete();
        
            return redirect()->route('setting.index')->with('success','Horario "' . $nombreSchedule . '" eliminado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar horario: ' . $e->getMessage());
            return redirect()->route('setting.index')
                ->withErrors(['error' => 'Error al eliminar el horario: ' . $e->getMessage()])
                ->with('error_message', 'Error al eliminar el horario: ' . $e->getMessage());
        }
    }

    public function storeGroup(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'boolean',
        ]);
        
        // Validar que no exista otro grupo con el mismo nombre
        $query = Group::where('name', $request->name);
        if($request->id != ""){
            $query->where('id', '!=', $request->id);
        }
        $exists = $query->exists();
        
        if($exists){
            return redirect()->route('setting.index')
                ->withErrors(['group_name' => 'Ya existe un grupo con ese nombre.'])
                ->withInput();
        }
        
        if($request->id != ""){
            $group = Group::findOrFail($request->id);
            $group->update($request->only(['name', 'active']));
        }else{
            Group::create($request->only(['name', 'active']));
        }
        return redirect()->route('setting.index')->with('success','Grupo guardado correctamente');
    }
    
    public function destroyGroup($id){
        try {
        $group = Group::findOrFail($id);
        
        // Verificar si está en uso en matriculas (el grupo se guarda como string en numero_grupo)
        $inUse = Matricula::where('numero_grupo', $group->name)->exists();
        
        if($inUse){
            return redirect()->route('setting.index')
                    ->withErrors(['error' => 'No se puede eliminar el grupo porque está siendo usado en matrículas registradas.'])
                    ->with('error_message', 'No se puede eliminar el grupo "' . $group->name . '" porque está siendo usado en matrículas registradas.');
        }
        
            $nombreGroup = $group->name;
        $group->delete();
        
            return redirect()->route('setting.index')->with('success','Grupo "' . $nombreGroup . '" eliminado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar grupo: ' . $e->getMessage());
            return redirect()->route('setting.index')
                ->withErrors(['error' => 'Error al eliminar el grupo: ' . $e->getMessage()])
                ->with('error_message', 'Error al eliminar el grupo: ' . $e->getMessage());
        }
    }

    public function storeTeacher(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'boolean',
        ]);
        
        // Validar que no exista otro docente con el mismo nombre
        $query = Teacher::where('name', $request->name);
        if($request->id != ""){
            $query->where('id', '!=', $request->id);
        }
        $exists = $query->exists();
        
        if($exists){
            return redirect()->route('setting.index')
                ->withErrors(['teacher_name' => 'Ya existe un docente con ese nombre.'])
                ->withInput();
        }
        
        if($request->id != ""){
            $teacher = Teacher::findOrFail($request->id);
            $teacher->update($request->only(['name', 'active']));
        }else{
            Teacher::create([
                'name' => $request->name,
                'active' => $request->active ?? true,
            ]);
        }
        return redirect()->route('setting.index')->with('success','Docente guardado correctamente');
    }
    
    public function destroyTeacher($id){
        try {
        $teacher = Teacher::findOrFail($id);
        
        // Los docentes solo se usan para generar planillas de asistencia, no se guardan en BD
        // Por lo tanto, se puede eliminar siempre
            $nombreTeacher = $teacher->name;
        $teacher->delete();
        
            return redirect()->route('setting.index')->with('success','Docente "' . $nombreTeacher . '" eliminado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar docente: ' . $e->getMessage());
            return redirect()->route('setting.index')
                ->withErrors(['error' => 'Error al eliminar el docente: ' . $e->getMessage()])
                ->with('error_message', 'Error al eliminar el docente: ' . $e->getMessage());
        }
    }

    public function storeModule(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);
        
        // Validar que no exista otro módulo con el mismo nombre
        $query = Module::where('name', $request->name);
        if($request->id != ""){
            $query->where('id', '!=', $request->id);
        }
        $exists = $query->exists();
        
        if($exists){
            return redirect()->route('setting.index')
                ->withErrors(['module_name' => 'Ya existe un módulo con ese nombre.'])
                ->withInput();
        }
        
        if($request->id != ""){
            $module = Module::findOrFail($request->id);
            $module->update($request->only(['name', 'code', 'active']));
        }else{
            Module::create($request->only(['name', 'code', 'active']));
        }
        return redirect()->route('setting.index')->with('success','Módulo guardado correctamente');
    }
    
    public function destroyModule($id){
        try {
        $module = Module::findOrFail($id);
        
        // Los módulos solo se usan para generar planillas de asistencia, no se guardan en BD
        // Por lo tanto, se puede eliminar siempre
            $nombreModule = $module->name;
        $module->delete();
        
            return redirect()->route('setting.index')->with('success','Módulo "' . $nombreModule . '" eliminado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar módulo: ' . $e->getMessage());
            return redirect()->route('setting.index')
                ->withErrors(['error' => 'Error al eliminar el módulo: ' . $e->getMessage()])
                ->with('error_message', 'Error al eliminar el módulo: ' . $e->getMessage());
        }
    }

    public function storeInstitution(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'logo_path' => 'nullable|string|max:255',
            'institucion_subtitulo' => 'nullable|string|max:255',
            'sede' => 'nullable|string|max:255',
            'nit' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'telefono2' => 'nullable|string|max:255',
            'telefono3' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'footer_licencia_texto' => 'nullable|string',
            'footer_ciudad' => 'nullable|string|max:255',
            'footer_mostrar_ubicacion_fecha' => 'nullable|boolean',
            'footer_firma' => 'nullable|string|max:255',
        ]);
        
        $institucion = InstitutionSetting::getSettings();
        
        // Manejar upload de logo si se proporciona
        $logoPath = $institucion->logo_path;
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = 'logo_' . time() . '.' . $logo->getClientOriginalExtension();
            $logoPath = 'dimages/' . $logoName;
            $logo->move(public_path('dimages'), $logoName);
        } elseif ($request->filled('logo_path')) {
            $logoPath = $request->logo_path;
        }
        
        $data = $request->only([
            'name', 
            'institucion_subtitulo',
            'sede',
            'nit', 
            'address', 
            'phone',
            'telefono2',
            'telefono3',
            'email', 
            'website', 
            'footer_licencia_texto', 
            'footer_ciudad',
            'footer_firma'
        ]);
        $data['logo_path'] = $logoPath;
        $data['footer_mostrar_ubicacion_fecha'] = $request->has('footer_mostrar_ubicacion_fecha') ? true : false;
        $institucion->update($data);
        
        // Limpiar cache de configuración de institución
        \Illuminate\Support\Facades\Cache::forget('institution_settings');
        
        return redirect()->route('setting.index')->with('success','Configuración de institución guardada correctamente');
    }

    
}
