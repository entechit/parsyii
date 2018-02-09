/* BEGIN ВЫБОРКИ ДАННЫХ */
SELECT * FROM INFORMATION_SCHEMA.PROCESSLIST

SELECT * FROM parsyii.source_site where ss_cust_id = 4;

SELECT * FROM parsyii.source_price where price_cust_id = 1 and price_modelcode = '4969363027719';
SELECT * FROM parsyii.source_price where price_id =21595

Select * from link_price_source_page  where lpsp_sp_id in (730495)

SELECT * FROM parsyii.source_page where sp_ss_id = 26 and /*sp_errors is not null sp_dp_id is null   and */ sp_parsed = 0 ;
SELECT * FROM parsyii.source_page where sp_ss_id = 16 and  sp_id in (228949);
SELECT * FROM parsyii.source_page where sp_ss_id = 22  /*and sp_dp_id is not null  and sp_seek_urls = 1 */ and sp_url /*in ('http://borika.ua//ru/node/119','http://borika.ua/ru/node/119')*/  like '%http://weekender.ua/main/item/164%';
SELECT * FROM parsyii.source_page order by sp_id desc limit 108

Select * from source_page sp where sp.sp_ss_id = 26 and not exists (Select 1 from result_data rd where rd.rd_ss_id = 26 and rd.rd_sp_id = sp.sp_id)


SELECT distinct sp_dp_id FROM parsyii.source_page where sp_ss_id = 46 

SELECT * FROM parsyii.result_data where (rd_ss_id=26 or rd_ss_id=16) and rd_short_data like 'https://images-fe%' order by rd_id desc;
Select * FROM parsyii.result_data where rd_ss_id=16 and /*rd_dt_id=8*/ rd_sp_id in  (228949) order by rd_id desc;

/* выгрузка описаний дл яперевода с японского */
Select rd2.rd_id, rd2.rd_sp_id, rd2.rd_short_data, rd10.rd_long_data, rd21.rd_long_data, rd1839.rd_short_data 
FROM parsyii.result_data rd2 
left join (select * from parsyii.result_data where rd_dt_id = 10) rd10 on rd2.rd_sp_id = rd10.rd_sp_id
left join (select * from parsyii.result_data where rd_dt_id = 21) rd21 on rd2.rd_sp_id = rd21.rd_sp_id
left join (select * from parsyii.result_data where rd_dt_id = 1839) rd1839 on rd2.rd_sp_id = rd1839.rd_sp_id
where rd2.rd_ss_id=16 
  and rd2.rd_dt_id = 2
  order by rd2.rd_sp_id
 
 (2, 10, 21, 1839) and rd_parentchild_seria = 0 order by rd_sp_id, rd_dt_id



Select * FROM parsyii.result_data order by rd_id desc limit 600

SELECT distinct rd_sp_id FROM parsyii.result_data where rd_ss_id=26

SELECT * FROM parsyii.result_img  where ri_rd_id = 93828  ri_ss_id = 26 and ri_img_name is null

SELECT * FROM parsyii.pars_rule where pr_dp_id = 53;


Select * from t_trace Where trace_comment like '%2.3 root_value%' or trace_comment like  '%2.5 p_value %';

select distinct rd.*, ri.ri_img_path, dt.dt_is_img, dt.dt_rd_field, ri.ri_img_name, ri_img_path
                from result_data rd
                left join result_img ri  on rd.rd_id = ri.ri_rd_id
                left join dir_tags dt on rd.rd_dt_id = dt.dt_id
                inner join export_link_tag_field eltf on eltf.eltf_dt_id = rd.rd_dt_id
                where rd.rd_sp_id = 228747 
                    and rd.rd_parentchild_seria = 0 
                     and eltf.eltf_ec_id = 2 and eltf.eltf_id is not null
                



/* END ВЫБОРКИ ДАННЫХ */


/* Настройка экспорта */
	/* выборки */
SELECT * FROM parsyii.export_customer
SELECT * FROM parsyii.export_cms_field where ecf_ec_id = 1 order by ecf_id;  /*ec=3 opencart продукты*/
SELECT * FROM parsyii.export_link_tag_field where  eltf_ec_id = 1;
SELECT * FROM parsyii.export_defaults ed where ed.ed_ss_id = 16;


SELECT * FROM parsyii.export_defaults ed
left join export_cms_field ecf on ecf.ecf_id = ed.ed_ecf_id
where ed_ss_id = 46;


	/* контроль полноты */
# выбор НЕ использованных тэгов в экспорте
Select distinct dt.dt_id, dt.dt_name, dt.dt_is_img
from  result_data rd
left join dir_tags dt on rd.rd_dt_id = dt.dt_id
left join export_link_tag_field eltf on eltf.eltf_dt_id = dt.dt_id
WHERE rd.rd_ss_id = 16 and eltf.eltf_ecf_id is null ;

# привязаннеые ссылки
Select * from export_link_tag_field where eltf_ec_id = 2;

# поля экпорта
Select * from export_cms_field where ecf_ec_id = 2;

# константы 16 заказа - катушки японец
SELECT * FROM `parsyii`.`export_defaults` where ed_ss_id in (45, 22) order by ed_ss_id, ed_ecf_id;

Select distinct dt_id, dt_name from dir_tags dt
left join result_data rd on rd.rd_dt_id = dt.dt_id
where rd.rd_ss_id = 45



/* Японец - формирование JAN-CODE */
	/*  ФОРМИРУЕМ JAN-CODE для тех товаров, у которых его еще нет*/
INSERT INTO Result_data (rd_ss_id, rd_dt_id, rd_short_data, rd_parentchild_seria, rd_sp_id)
Select 16, 1527,  concat('4969363',replace(rd_short_data, ' ','')), rd_parentchild_seria, rd_sp_id from result_data where 
rd_ss_id = 16 and
rd_dt_id = 8 and 
replace(rd_short_data, ' ','') not in (Select SUBSTR(replace(rd_short_data, ' ',''),8)  from result_data where 
rd_ss_id = 16 and
rd_dt_id = 1527)

/* дублирование данных тега с тарнсофрмацией */
INSERT INTO Result_data (rd_ss_id, rd_dt_id, rd_short_data, rd_parentchild_seria, rd_sp_id)
Select 16, 1926,  replace(rd_short_data, 'SHIMANO ',''), rd_parentchild_seria, rd_sp_id from result_data rd
where rd.rd_ss_id = 16 and rd.rd_dt_id = 2


	/* добавляем в прайс те товары, которых еще нет  сравнивая по артикулу модели*/
INSERT INTO source_price (price_cust_id, price_modelcode)
Select 1, rd_short_data from result_data where 
rd_ss_id = 16 and
rd_dt_id = 1527 and 
rd_short_data not in (Select price_modelcode  from source_price where 
price_cust_id = 1)

/* проверяем на наличие ошибочных кодов*/
Select * from result_data Where 
rd_short_data like '%3%'
and rd_ss_id = 16



/*   Перемещаем картинки из одного источника в картинки другого (из амазона японцу) */

Create temporary table temp_jan (sp_id int, price_modelcode varchar(20), rd_sp_id int);
Truncate table temp_jan;

Select * from temp_jan;

Insert into temp_jan 
Select lpsp.lpsp_sp_id sp_id, price.price_modelcode, rd.rd_sp_id 
from result_data rd, source_price price 
inner join link_price_source_page lpsp on price.price_id = lpsp.lpsp_price_id
where rd.rd_short_data = price.price_modelcode and 
price.price_cust_id = 1 and rd.rd_sp_id in (select sp_id from temp_id)


update result_data rd set rd.rd_sp_id = (select tj.rd_sp_id from temp_jan tj where tj.sp_id=rd.rd_sp_id)
where rd.rd_ss_id=26 and rd.rd_id>0


Update result_img set ri_ss_id = 16 where ri_ss_id = 26
Update result_data set rd_ss_id = 16 where rd_ss_id = 26


select rd.*, tj.sp_id from result_data rd left join temp_jan tj on tj.sp_id=rd.rd_sp_id
where rd.rd_ss_id=26 and  tj.sp_id is null



Select * from result_data where rd_dt_id = 1527  rd_short_data = '03637 7'

Update source_page set sp_parsed = 0, sp_errors=null where sp_ss_id = 26

Delete from result_data where rd_ss_id = 16 and rd_dt_id = 9 and rd_short_data like 'https://images-fe%'
Delete from result_data where rd_ss_id = 16 and rd_dt_id = 9

/* замена имени по ID строки*/
Update result_data set rd_short_data = 'SHIMANO 92 CLUB DEMI' where rd_id =74357;

/* Дописываем категорию по ID продукта  */
Insert into result_data (rd_dt_id, rd_ss_id, rd_sp_id, rd_short_data) 
Select 1839, 22, sp.sp_id,'133'  
from export_id ei, source_page sp left join result_data rd on sp.sp_id = rd.rd_sp_id 
where rd.rd_ss_id=22 and sp.sp_ss_id=22 and ei.ei_url = sp.sp_url and rd.rd_dt_id = 7 and ei.ei_product_id =514;

/* Дописываем категорию по ID  страницы источника с серией = 0*/
Insert into result_data (rd_dt_id, rd_ss_id, rd_sp_id, rd_short_data) Select 1839, 16, 112, '133'  from dual;

/* добавляем картинку для категории */ 
Insert into result_data (rd_ss_id,rd_sp_id,rd_dt_id,rd_short_data,rd_parentchild_seria)
select rd_ss_id,rd_sp_id,rd_dt_id,rd_short_data,0 from result_data
where rd_ss_id=16 and rd_dt_id=9 and rd_parentchild_seria=1

Insert into result_img (ri_rd_id,ri_source_url,ri_img_name,ri_img_path,ri_alt,ri_title,ri_ss_id)
select rd_id,rd_short_data,ri.ri_img_name,ri.ri_img_path,ri.ri_alt,ri.ri_title,ri.ri_ss_id 
from result_data rd inner join  result_img ri on (rd.rd_id=ri.ri_rd_id)
where rd_ss_id=16 and rd_dt_id=9 and rd_parentchild_seria=1


/*  Удаление всех кроме 1 минимальной записи */

Create temporary table temp_id (rd_id int);
Insert into temp_id 
Select a.rd_id from (Select min(rd_id) rd_id, rd_sp_id from result_data 
where rd_ss_id = 16
and rd_dt_id = 9
and rd_parentchild_seria = 0
group by rd_sp_id) a;

Delete from result_data 
where rd_ss_id = 16
and rd_dt_id = 9
and rd_parentchild_seria = 0
and rd_id not in 
(Select rd_id from temp_id );


/* добавляем категории в товары, у которых серия не 0, старвниваем по имени категории из серии  = 0*/
Set @pcs = 22;
Insert into result_data (rd_dt_id, rd_ss_id, rd_sp_id, rd_parentchild_seria, rd_short_data) 
Select 1839, 16, rd.rd_sp_id, @pcs, 278  
from result_data rd 
where rd.rd_ss_id=16 and rd.rd_dt_id = 1926 and rd.rd_short_data = '11 ALIVIO' 
and rd.rd_sp_id in (Select rdd.rd_sp_id from result_data rdd where rdd.rd_sp_id = rd.rd_sp_id and rdd.rd_parentchild_seria = @pcs);
