# NexusPHP 分类上传倍率

适用于 NexusPHP 1.10.2 / PHP 8.2–8.5 / Filament 5。

## 功能
后台“系统设置 → 分类上传倍率”按分类 ID 添加规则。每条规则支持“原有倍率 × 分类倍率”和“以 1 为基础 × 分类倍率”。例如原有倍率 2、分类倍率 2，结果分别为 4 倍和 2 倍。列表标题右侧显示 `x2` 标签并提示计算方式。

## 安装
```sh
git clone https://github.com/fei152/nexusphp-category-upload-multiplier.git packages/nexus-category-upload-multiplier
composer config repositories.category-upload-multiplier path packages/nexus-category-upload-multiplier
composer require 'xiaomlove/nexusphp-category-upload-multiplier:dev-main'
php artisan plugin install xiaomlove/nexusphp-category-upload-multiplier
php artisan optimize:clear
```

插件使用 `torrent_upload_multiplier` 与 `torrent_list_before_rating` 两个钩子。若站点核心没有这两个钩子，先执行：
```sh
git apply --check packages/nexus-category-upload-multiplier/patches/nexusphp-1.10.2.patch
git apply packages/nexus-category-upload-multiplier/patches/nexusphp-1.10.2.patch
```
补丁只添加两行接入代码，不覆盖核心文件或其他插件。已有规则缺少 `mode` 时默认使用原有倍率叠乘。

未配置分类保持原逻辑，历史上传不追溯；下载量、真实上传统计和盒子限制沿用系统逻辑。重复安装不会覆盖已有规则。
