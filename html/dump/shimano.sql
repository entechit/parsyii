/* Выбираем новые ссылки по катушкам product/reel */
SET @source_pages = 18;
SET @dest_pages = 16;

insert into parsyii.source_page (sp_ss_id,sp_url,sp_datetimeadd)
select @dest_pages,sp_url,sp_datetimeadd 
from parsyii.source_page ss
where ss.sp_ss_id=@source_pages and (ss.sp_url like '%product//reel/%' or ss.sp_url like '%product/reel/%') 
and ss.sp_url not like '%freeArea%'
and ss.sp_url not like '%.html%'
and ss.sp_url not like '%?_ga%'
and ss.sp_errors is null
and not exists (select 1 from parsyii.source_page sd where sd.sp_ss_id=@dest_pages and sd.sp_url = ss.sp_url)

/* проверяем коды на повторы (после добавления новых страниц не должно быть дублей кодов) */
Select rd_short_data,count(*)
from parsyii.result_data 
where rd_ss_id = @dest_pages and rd_dt_id=8
group by rd_short_data
having count(*) > 1

/* Выгружаем все японо-содержащие данные для перевода */
select rd.rd_id, dt.dt_name, rd.rd_short_data, sp.sp_url  
from parsyii.result_data rd
inner join parsyii.source_page sp on sp.sp_id = rd.rd_sp_id
inner join parsyii.dir_tags dt on dt.dt_id = rd.rd_dt_id
where rd.rd_ss_id=@dest_pages and rd.rd_dt_id not in (17,755,756)
and rd.rd_short_data NOT REGEXP '^[A-Za-z0-9\.,、,/\,_,:,－,+,%,\',@&\(\) \-]*$' 

/* Апдейтим японо-содержащиеся для перевод */

/* Проверяем DT =  8 на отсутствие лишних знаков (звездочки) в номере - Убираем */
update parsyii.result_data
set rd_short_data = replace(rd_short_data, '*', '')  
where sp_ss_id=@dest_pages and rd_dt_id = 8

/* Формируем полный JAN-code из краткого DT = 8 в тег DT = 1527 */
insert into parsyii.result_data (rd_ss_id,rd_sp_id,rd_dt_id,rd_short_data,rd_parentchild_seria)
select rd_ss_id,rd_sp_id,1527,concat('4969363',replace(rd_short_data, '.','')),rd_parentchild_seria
from parsyii.result_data 
where rd_ss_id=@dest_pages and rd_dt_id=8

/* проверяем JAN-CODE - должен быть один на серию */
Select rd_sp_id,rd_parentchild_seria,count(*)
from parsyii.result_data 
where rd_ss_id = @dest_pages and rd_dt_id=1527
group by rd_sp_id,rd_parentchild_seria
having count(*) > 1

/* если есть лишние - удаляем */
Create temporary table parsyii.tmp_id3 (rd_id int);
Insert into parsyii.tmp_id3 
Select a.rd_id from (
Select min(rd_id) rd_id, rd_sp_id, rd_parentchild_seria 
from result_data where rd_ss_id = @dest_pages and rd_dt_id = 1527
group by rd_sp_id, rd_parentchild_seria) a;
Delete from parsyii.result_data 
where rd_ss_id = @dest_pages and rd_dt_id = 1527 and rd_id not in (Select rd_id from parsyii.tmp_id3);

/* Формируем в таблице прайса код для поиска на амазон */
INSERT INTO parsyii.source_price (price_cust_id, price_modelcode) 
Select 1, rd_short_data from parsyii.result_data 
where rd_ss_id = @dest_pages and rd_dt_id = 1527 
and rd_short_data not in (Select price_modelcode from parsyii.source_price where price_cust_id = 1)

/* Перемещаем Картинки из SS_id = 26 в основную коллекцию */
/* 1. проставляем серию в источнике 26 из источника 16 */
Create temporary table parsyii.temp_id (sp_id int, rd_sp_id int, parentchild_seria int, rd_short_data varchar(1000));
insert into parsyii.temp_id 
select distinct sp.sp_id,dd.rd_sp_id,dd.rd_parentchild_seria,dd.rd_short_data
from parsyii.source_page sp inner join parsyii.result_data d on sp.sp_id=d.rd_sp_id
inner join parsyii.link_price_source_page l on l.lpsp_sp_id=sp.sp_id
inner join parsyii.source_price p on p.price_id=l.lpsp_price_id
inner join parsyii.result_data dd on dd.rd_short_data = p.price_modelcode
where sp.sp_ss_id=26 and d.rd_parentchild_seria = 0
and dd.rd_ss_id=@dest_pages and dd.rd_dt_id=1527

update parsyii.result_data d set d.rd_parentchild_seria = 
(select dd.rd_parentchild_seria from parsyii.temp_id dd where dd.sp_id = d.rd_sp_id)
where d.rd_ss_id=26

/* 2. меняем страницу в источнике 26 на страницу из источника 16 */
Create temporary table parsyii.tmp_id (sp_id int, rd_sp_id int);
insert into parsyii.tmp_id 
select distinct sp_id,rd_sp_id from parsyii.temp_id;
update parsyii.result_data d set rd_sp_id = 
(select dd.rd_sp_id from parsyii.tmp_id dd where dd.sp_id = d.rd_sp_id)
where d.rd_ss_id=26;

/* 3. переносим результат из источника 26 в источник 16 */
update parsyii.result_data set rd_ss_id = @dest_pages where rd_ss_id = 26;
update parsyii.result_img  set ri_ss_id=@dest_pages where  ri_ss_id=26;

/* Выбираем картинку для подкатегории в тег 1928 */
insert into parsyii.result_data (rd_ss_id,rd_sp_id,rd_dt_id,rd_short_data,rd_parentchild_seria)
select rd_ss_id,rd_sp_id,1928,rd_short_data,0
from parsyii.result_data 
where rd_ss_id=@dest_pages and rd_dt_id=9 and rd_parentchild_seria=1
and rd_sp_id in (select sp_id from parsyii.tmp_id);

/* оставляем только одну картинку для подкатегории */
Create temporary table parsyii.tmp_id2 (rd_id int);
Insert into parsyii.tmp_id2 
Select a.rd_id from (
Select min(rd_id) rd_id, rd_sp_id 
from parsyii.result_data where rd_ss_id = @dest_pages and rd_dt_id = 1928
group by rd_sp_id) a;
Delete from parsyii.result_data 
where rd_ss_id = @dest_pages and rd_dt_id = 1928 and rd_id not in (Select rd_id from parsyii.tmp_id2);

insert into parsyii.result_img (ri_rd_id,ri_source_url,ri_img_name,ri_img_path,ri_alt,ri_title,ri_ss_id)
select rd.rd_id,rd.rd_short_data,ri.ri_img_name,ri.ri_img_path,ri.ri_alt,ri_title,ri.ri_ss_id 
FROM parsyii.result_data rd
inner join (select rd_id,rd_sp_id,rd_short_data 
FROM parsyii.result_data where rd_ss_id=@dest_pages and rd_dt_id=9 and rd_parentchild_seria=1) rdd on (rd.rd_sp_id = rdd.rd_sp_id and rd.rd_short_data = rdd.rd_short_data)
inner join parsyii.result_img ri on (rdd.rd_id = ri.ri_rd_id)
where rd.rd_ss_id=@dest_pages and rd.rd_dt_id=1928
and rd.rd_sp_id in (select sp_id from parsyii.tmp_id);

/* проставление подкатегории для товара */
insert into parsyii.result_data (rd_ss_id,rd_sp_id,rd_dt_id,rd_short_data)
select @dest_pages, sp.sp_id, 1839, min(ei.ei_product_id) ei_product_id
from parsyii.export_id ei inner join parsyii.source_page sp on ei.ei_url = sp.sp_url
where ei.ei_ec_id=2 and ei.ei_rd_parentchild_seria=0 and sp.sp_ss_id=@dest_pages
group by sp.sp_id;

/* убираем точку в ценах */
update parsyii.result_data set rd_short_data = replace(rd_short_data,',','') 
where rd_ss_id=@dest_pages and rd_dt_id=20;

/* В тег 1929 формируем имя товара - данные из тегов 2 и 12 */
insert into parsyii.result_data (rd_ss_id,rd_sp_id,rd_dt_id,rd_short_data,rd_parentchild_seria)
select rd.rd_ss_id, rd.rd_sp_id,1929, concat_ws(" ",'SHIMANO',rdd.rd_short_data,rd.rd_short_data),rd.rd_parentchild_seria 
from parsyii.result_data rd
inner join (select rd_sp_id,rd_short_data 
FROM parsyii.result_data where rd_ss_id=@dest_pages and rd_dt_id=2) rdd on (rd.rd_sp_id = rdd.rd_sp_id)
where rd.rd_ss_id=@dest_pages and rd.rd_dt_id=12 and 
concat_ws('_',rd.rd_sp_id,rd.rd_parentchild_seria) not 
in (Select concat_ws('_',rd1.rd_sp_id,rd1.rd_parentchild_seria) from result_data rd1 Where rd1.rd_ss_id = @dest_pages and rd1.rd_dt_id=1929 )

/* вариант с исключением повтора в названии */
insert into parsyii.result_data (rd_ss_id,rd_sp_id,rd_dt_id,rd_short_data,rd_parentchild_seria)
select rd.rd_ss_id, rd.rd_sp_id,1929, 
IF (INSTR(rdd.rd_short_data, rd.rd_short_data) = 0, 
concat(rdd.rd_short_data,' ',rd.rd_short_data), rdd.rd_short_data)
rd.rd_parentchild_seria 
from parsyii.result_data rd
inner join (select rd_sp_id,rd_short_data 
FROM parsyii.result_data where rd_ss_id=@dest_pages and rd_dt_id=2) rdd on (rd.rd_sp_id = rdd.rd_sp_id)
where rd.rd_ss_id=@dest_pages and rd.rd_dt_id=12

/* добавляем тег Количество подшипников для экспорта */
insert into parsyii.result_data (rd_ss_id,rd_sp_id,rd_dt_id,rd_short_data,rd_parentchild_seria)
select rd_ss_id,rd_sp_id,24,rd_short_data,rd_parentchild_seria
from parsyii.result_data 
where rd_ss_id=@dest_pages and rd_dt_id in (754,1476,1490)
and concat_ws('_',rd.rd_sp_id,rd.rd_parentchild_seria) not in
 (Select concat_ws('_',rd_sp_id,rd_parentchild_seria) from result_data Where rd_ss_id = @dest_pages and rd_dt_id in (754,1476,1490))
/* проверка */
select rd_sp_id,rd_dt_id,rd_parentchild_seria,count(*)
from parsyii.result_data 
where rd_ss_id=@dest_pages and rd_dt_id=24
group by rd_sp_id,rd_dt_id,rd_parentchild_seria
having count(*) > 1

select rd.rd_id,rd.rd_sp_id,rd.rd_short_data,rdd.rd_short_data jan,r.rd_short_data short_jan,rddd.rd_short_data category
FROM parsyii.result_data rd 
inner join (select rd_id,rd_sp_id,rd_short_data,rd_parentchild_seria 
FROM parsyii.result_data where rd_ss_id=16 and rd_dt_id=1527) rdd on (rd.rd_sp_id = rdd.rd_sp_id and rd.rd_parentchild_seria = rdd.rd_parentchild_seria)
inner join (select rd_id,rd_sp_id,rd_short_data 
FROM parsyii.result_data where rd_ss_id=16 and rd_dt_id=1926) rddd on (rd.rd_sp_id = rddd.rd_sp_id)
inner join (select rd_id,rd_sp_id,rd_short_data,rd_parentchild_seria 
FROM parsyii.result_data where rd_ss_id=16 and rd_dt_id=8) r on (rd.rd_sp_id = r.rd_sp_id and rd.rd_parentchild_seria = r.rd_parentchild_seria)
where rd.rd_ss_id=16 and rd.rd_dt_id=1929
and rd.rd_sp_id >=2007744; 


