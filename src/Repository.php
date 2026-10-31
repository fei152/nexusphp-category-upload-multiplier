<?php
namespace NexusPlugin\CategoryUploadMultiplier;
use App\Models\Setting;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs\Tab;
use Nexus\Plugin\BasePlugin;

class Repository extends BasePlugin {
 public const ID='category-upload-multiplier'; public const VERSION='1.0.0'; public const COMPATIBLE_NP_VERSION='1.10.2';
 public function install(): void { $this->ensureServiceProviderRegistered(); Setting::query()->firstOrCreate(['name'=>'category_upload_multiplier.rules'],['value'=>json_encode([]),'autoload'=>'yes']); }
 public function uninstall(): void { Setting::query()->where('name','category_upload_multiplier.rules')->delete(); }
 public function boot(): void { add_filter('nexus_setting_tabs',[$this,'addSettingTab']); add_filter('torrent_upload_multiplier',[$this,'applyMultiplier'],10,3); add_filter('torrent_list_before_rating', [$this, 'renderMultiplier'], 10, 2); }
 public function renderMultiplier(string $html, array $torrent): string
 {
     $id = (int)($torrent['category'] ?? $torrent['category_id'] ?? 0);
     if ($id < 1) return $html;
     foreach ((array)Setting::get('category_upload_multiplier.rules', []) as $rule) {
         if (!is_array($rule) || (int)($rule['category_id'] ?? 0) !== $id || !is_numeric($rule['multiplier'] ?? null)) continue;
         $multiplier = max(0, (float)$rule['multiplier']);
         if (!is_finite($multiplier)) return $html;
         $label = htmlspecialchars('x' . rtrim(rtrim(number_format($multiplier, 4, '.', ''), '0'), '.'), ENT_QUOTES, 'UTF-8');
         $mode = ($rule['mode'] ?? 'multiply') === 'replace' ? '以 1 为基础' : '原有上传倍率';
         $title = htmlspecialchars('分类上传倍率：' . $mode . ' × ' . $multiplier, ENT_QUOTES, 'UTF-8');
         return $html . '<td class="embedded" style="width:1%;white-space:nowrap;padding:0 6px;text-align:center;vertical-align:middle"><span class="category-upload-multiplier" title="' . $title . '" style="display:inline-block;color:#157347;background:#e8f5e9;border:1px solid #81c784;border-radius:4px;padding:1px 5px;font-weight:bold;font-size:12px;line-height:18px">' . $label . '</span></td>';
     }
     return $html;
 }
 public function getId(): string { return self::ID; }
 public function trans($name): string { return nexus_trans(self::ID.'::messages.'.$name); }
 public function addSettingTab(array $tabs): array { $tabs[]=Tab::make($this->trans('settings.tab'))->id('category_upload_multiplier')->schema([Repeater::make('category_upload_multiplier.rules')->label($this->trans('settings.rules'))->helperText($this->trans('settings.rules_help'))->schema([TextInput::make('category_id')->integer()->minValue(1)->required()->label($this->trans('settings.category_id')),TextInput::make('multiplier')->numeric()->minValue(0)->required()->default(1)->label($this->trans('settings.multiplier')),Select::make('mode')->options(['multiply'=>$this->trans('settings.mode_multiply'),'replace'=>$this->trans('settings.mode_replace')])->default('multiply')->required()->label($this->trans('settings.mode'))->helperText($this->trans('settings.mode_help'))])->columns(3)->default([])->columnSpanFull()]); return $tabs; }
 public function applyMultiplier(float $ratio, array $torrent, array $user=[]): float { $id=(int)($torrent['category']??$torrent['category_id']??0); foreach ((array)Setting::get('category_upload_multiplier.rules',[]) as $r) if ((int)($r['category_id']??0)===$id && is_numeric($r['multiplier']??null)) return (($r['mode']??'multiply')==='replace'?1:$ratio)*max(0,(float)$r['multiplier']); return $ratio; }
 private function ensureServiceProviderRegistered(): void { $p=config_path('app.php'); if (!is_file($p)) return; $n='NexusPlugin\\CategoryUploadMultiplier\\ServiceProvider::class'; $c=file_get_contents($p); if ($c===false||str_contains($c,$n)) return; $needle='        /*'.PHP_EOL.'         * Package Service Providers...'; file_put_contents($p,str_replace($needle,"        {$n},".PHP_EOL.PHP_EOL.$needle,$c)); }
}
