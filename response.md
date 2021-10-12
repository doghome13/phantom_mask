# Response
  * 開發環境：Laravel 5.6、PHP 7.2、MySQL、Nginx
  * 前端： Vue.js 3 [https://github.com/doghome13/mask-vue](https://github.com/doghome13/mask-vue)

## Required
### API Document
  Import [data/postman.json](storage/data/phantom_mask.postman_collection.json) json file to Postman

### Import Data Commands
  `php artisan migrate`\
  `php artisan seed:json`

### Tables
  * masks: 口罩名稱、顏色
  * opening_hours: 營業日、營業時間
  * pharmacies: 藥局名稱、收支
  * products: 各藥局販售的口罩，其價格
  * purchase_histories: 顧客的消費紀錄，交易日期、交易金額、商品 id、藥局 id
  * users: 顧客名稱、收支

## Bonus
### Demo Site Url
  demo site is ready on [https://tako-phantom-mask.herokuapp.com](https://tako-phantom-mask.herokuapp.com/#/pharmacy/list/period)\
  備註：測試網站的資料庫無法建立 `fulltext` 索引，搜尋功能無法在測試網站呈現
