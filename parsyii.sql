-- phpMyAdmin SQL Dump
-- version 4.5.3.1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Окт 06 2017 г., 18:04
-- Версия сервера: 5.7.12
-- Версия PHP: 7.0.2

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `parsyii`
--

-- --------------------------------------------------------

--
-- Структура таблицы `dir_cms`
--

DROP TABLE IF EXISTS `dir_cms`;
CREATE TABLE `dir_cms` (
  `dc_id` int(11) NOT NULL,
  `dc_name` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='справочник конструкторов на которых построены сайты';

--
-- Дамп данных таблицы `dir_cms`
--

INSERT INTO `dir_cms` (`dc_id`, `dc_name`) VALUES
(1, 'Drupal'),
(2, 'WordPress'),
(3, 'DLE'),
(4, 'Joomla'),
(5, 'MODx'),
(6, 'Textpattern'),
(7, 'OSCommerce'),
(8, 'e107'),
(9, 'Danneo'),
(10, '1C:Битрикс'),
(11, 'NetCat'),
(12, 'TYPO3'),
(13, 'Plone'),
(14, 'CMS Made Simple'),
(15, 'Movable Type'),
(16, 'InstantCMS'),
(17, 'MaxSite CMS'),
(18, 'UMI.CMS'),
(19, 'HostCMS'),
(20, 'Amiro CMS'),
(21, 'Magento'),
(22, 'S.Builder'),
(23, 'ABO.CMS'),
(24, 'Twilight CMS'),
(25, 'PHP-Fusion'),
(26, 'Melbis'),
(27, 'Miva Merchant'),
(28, 'phpwcms'),
(29, 'N2 CMS'),
(30, 'Explay CMS'),
(31, 'ExpressionEngine'),
(32, 'Klarnet CMS'),
(33, 'SEQUNDA'),
(34, 'SiteDNK'),
(35, 'CM5'),
(36, 'Site Sapiens'),
(37, 'Cetera CMS'),
(38, 'Hitmaster'),
(39, 'DSite'),
(40, 'SiteEdit'),
(41, 'TrinetCMS'),
(42, 'Adlabs.CMS'),
(43, 'Introweb-CMS'),
(44, 'iNTERNET.cms'),
(45, 'Kentico CMS'),
(46, 'LiveStreet'),
(47, 'vBulletin'),
(48, 'phpBB'),
(49, 'Invision Power Board'),
(50, 'Cmsimple'),
(51, 'OpenCMS'),
(52, 'slaed'),
(53, 'PHP-Nuke'),
(54, 'RUNCMS'),
(55, 'eZ publish'),
(56, 'Koobi'),
(57, 'Simple Machines Forum (SMF)'),
(58, 'MediaWiki'),
(59, 'LightMon'),
(60, 'diafan.CMS'),
(61, 'ImageCMS'),
(62, 'ocStore'),
(63, 'Joostina'),
(64, 'PHPShop'),
(65, 'Santafox'),
(66, 'Webasyst'),
(67, 'OpenCart'),
(68, 'PrestaShop'),
(69, 'nopCommerce'),
(70, 'Самоделка Shimano JP');

-- --------------------------------------------------------

--
-- Структура таблицы `dir_page_cms`
--

DROP TABLE IF EXISTS `dir_page_cms`;
CREATE TABLE `dir_page_cms` (
  `dp_id` int(11) NOT NULL,
  `dp_dc_id` int(11) DEFAULT NULL COMMENT 'Тип CMS (справочник)',
  `dp_name` varchar(250) DEFAULT NULL COMMENT 'Обозначение вида страницы',
  `dp_descript` varchar(250) DEFAULT NULL COMMENT 'подробное описание'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Справочник типов страниц для разных CMS';

--
-- Дамп данных таблицы `dir_page_cms`
--

INSERT INTO `dir_page_cms` (`dp_id`, `dp_dc_id`, `dp_name`, `dp_descript`) VALUES
(1, 66, 'Описание фанатика', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `dir_tags`
--

DROP TABLE IF EXISTS `dir_tags`;
CREATE TABLE `dir_tags` (
  `dt_id` int(11) NOT NULL,
  `dt_name` varchar(250) DEFAULT NULL,
  `dt_rd_field` varchar(45) DEFAULT 'rd_short_data/rd_long_data ' COMMENT 'имя поля, в которое нужно запихнуть результат в таблицу result_data, это длинный текст или короткий, до 1000 символов',
  `dt_is_img` tinyint(4) DEFAULT '0' COMMENT '0/1 - является ли поле изображением и есть ли онем запись в result_img'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='имена полей которые анализируются системой';

--
-- Дамп данных таблицы `dir_tags`
--

INSERT INTO `dir_tags` (`dt_id`, `dt_name`, `dt_rd_field`, `dt_is_img`) VALUES
(1, 'title', 'rd_short_data/rd_long_data ', 0),
(2, 'cat_id', 'rd_short_data/rd_long_data ', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `pars_rule`
--

DROP TABLE IF EXISTS `pars_rule`;
CREATE TABLE `pars_rule` (
  `pr_id` int(11) NOT NULL,
  `pr_id_parent` int(11) DEFAULT NULL,
  `pr_dp_id` int(11) NOT NULL COMMENT 'Тип страницы из dir_page_cms',
  `pr_nodetype` char(1) DEFAULT NULL COMMENT 'q - query   n - node  i - item  h - innerHTML\n',
  `pr_selector` varchar(250) DEFAULT NULL COMMENT 'строка селектора',
  `pr_dt_id` int(11) DEFAULT NULL COMMENT 'название поля, в которое вытаскиваем данные'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='селекторы для обнаружения данных на странице';

-- --------------------------------------------------------

--
-- Структура таблицы `result_data`
--

DROP TABLE IF EXISTS `result_data`;
CREATE TABLE `result_data` (
  `rd_id` int(11) NOT NULL,
  `rd_ss_id` int(11) NOT NULL COMMENT 'код сайта, который анализировали',
  `rd_sp_id` int(11) NOT NULL COMMENT 'код страницы которую анализировали',
  `rd_dt_id` int(11) NOT NULL COMMENT 'имя результирующего поля, которое выгребли',
  `rd_short_data` varchar(1000) DEFAULT NULL COMMENT 'хранилище коротких данных',
  `rd_long_data` text COMMENT 'хранилище длинных данных'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='результат парсинга страниц';

-- --------------------------------------------------------

--
-- Структура таблицы `result_img`
--

DROP TABLE IF EXISTS `result_img`;
CREATE TABLE `result_img` (
  `ri_id` int(11) NOT NULL,
  `ri_rd_id` int(11) DEFAULT NULL COMMENT 'Ссылка на запись в  result_data',
  `ri_img_name` varchar(45) DEFAULT NULL,
  `ri_img_path` varchar(250) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Хранятся пути к сграбленым картинками';

-- --------------------------------------------------------

--
-- Структура таблицы `source_page`
--

DROP TABLE IF EXISTS `source_page`;
CREATE TABLE `source_page` (
  `sp_id` int(11) NOT NULL,
  `sp_ss_id` int(11) DEFAULT NULL COMMENT 'Код сайта',
  `sp_url` varchar(1000) DEFAULT NULL COMMENT 'адрес страницы',
  `sp_dp_id` int(11) DEFAULT NULL COMMENT 'тип страницы, определенный по признаку сущестования узлов DOM',
  `sp_parsed` tinyint(4) DEFAULT '0' COMMENT 'Страница проанализирована на данные -результаты в result_data',
  `sp_datetimeadd` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'дата добавления страницы в каталог'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='результат анализа сайта - найденные ссылки';

--
-- Дамп данных таблицы `source_page`
--

INSERT INTO `source_page` (`sp_id`, `sp_ss_id`, `sp_url`, `sp_dp_id`, `sp_parsed`, `sp_datetimeadd`) VALUES
(1890, 2, 'http://ukesa.com.ua/silikon-fanatik-boxer-2/', NULL, 0, '2017-10-03 15:57:41'),
(1891, 2, 'http://ukesa.com.ua/silikon-fanatik-boxer-3/', NULL, 0, '2017-10-03 15:57:41'),
(1892, 2, 'http://ukesa.com.ua/silikon-fanatik-boxer-35/', NULL, 0, '2017-10-03 15:57:42'),
(1893, 2, 'http://ukesa.com.ua/silikon-fanatik-boxer-45/', NULL, 0, '2017-10-03 15:57:42'),
(1894, 2, 'http://ukesa.com.ua/silikon-fanatik-classic-17/', NULL, 0, '2017-10-03 15:57:42'),
(1895, 2, 'http://ukesa.com.ua/silikon-fanatik-classic-29/', NULL, 0, '2017-10-03 15:57:42'),
(1896, 2, 'http://ukesa.com.ua/silikon-fanatik-dagger-/', NULL, 0, '2017-10-03 15:57:42'),
(1897, 2, 'http://ukesa.com.ua/silikon-fanatik-dagger-40/', NULL, 0, '2017-10-03 15:57:42'),
(1898, 2, 'http://ukesa.com.ua/silikon-fanatik-dagger-active-5/', NULL, 0, '2017-10-03 15:57:42'),
(1899, 2, 'http://ukesa.com.ua/silikon-fanatik-goby-/', NULL, 0, '2017-10-03 15:57:42'),
(1900, 2, 'http://ukesa.com.ua/silikon-fanatik-goby-45/', NULL, 0, '2017-10-03 15:57:42'),
(1901, 2, 'http://ukesa.com.ua/silikon-fanatik-jocker-4/', NULL, 0, '2017-10-03 15:57:42'),
(1902, 2, 'http://ukesa.com.ua/silikon-fanatik-larva-16/', NULL, 0, '2017-10-03 15:57:42'),
(1903, 2, 'http://ukesa.com.ua/silikon-fanatik-larva-2/', NULL, 0, '2017-10-03 15:57:42'),
(1904, 2, 'http://ukesa.com.ua/silikon-fanatik-larva-25/', NULL, 0, '2017-10-03 15:57:42'),
(1905, 2, 'http://ukesa.com.ua/silikon-fanatik-larva-30/', NULL, 0, '2017-10-03 15:57:42'),
(1906, 2, 'http://ukesa.com.ua/silikon-fanatik-larva-35/', NULL, 0, '2017-10-03 15:57:42'),
(1907, 2, 'http://ukesa.com.ua/silikon-fanatik-larva-45/', NULL, 0, '2017-10-03 15:57:42'),
(1908, 2, 'http://ukesa.com.ua/silikon-fanatik-lobster-2/', NULL, 0, '2017-10-03 15:57:42'),
(1909, 2, 'http://ukesa.com.ua/silikon-fanatik-lobster-36/', NULL, 0, '2017-10-03 15:57:42'),
(1910, 2, 'http://ukesa.com.ua/silikon-fanatik-mik-maus-16/', NULL, 0, '2017-10-03 15:57:42'),
(1911, 2, 'http://ukesa.com.ua/silikon-fanatik-mik-maus-20/', NULL, 0, '2017-10-03 15:57:42'),
(1912, 2, 'http://ukesa.com.ua/silikon-fanatik-mik-maus-25/', NULL, 0, '2017-10-03 15:57:42'),
(1913, 2, 'http://ukesa.com.ua/silikon-fanatik-mik-maus-30/', NULL, 0, '2017-10-03 15:57:42'),
(1914, 2, 'http://ukesa.com.ua/silikon-fanatik-mik-maus-35/', NULL, 0, '2017-10-03 15:57:42'),
(1915, 2, 'http://ukesa.com.ua/silikon-fanatik-rider-16/', NULL, 0, '2017-10-03 15:57:42'),
(1916, 2, 'http://ukesa.com.ua/silikon-fanatik-rider-22/', NULL, 0, '2017-10-03 15:57:42'),
(1917, 2, 'http://ukesa.com.ua/silikon-fanatik-viper-2/', NULL, 0, '2017-10-03 15:57:42'),
(1918, 2, 'http://ukesa.com.ua/silikon-fanatik-larva-3/', NULL, 0, '2017-10-03 15:57:42');

-- --------------------------------------------------------

--
-- Структура таблицы `source_site`
--

DROP TABLE IF EXISTS `source_site`;
CREATE TABLE `source_site` (
  `ss_id` int(11) NOT NULL,
  `ss_url` varchar(255) DEFAULT NULL COMMENT 'URL сайта',
  `ss_dc_id` int(11) DEFAULT NULL COMMENT 'тип CMS',
  `ss_descript` varchar(250) DEFAULT NULL COMMENT 'Описание для кого и зачем нам этот сайт',
  `ss_dateadd` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата добавления задания'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='источники сайтов для парсинга';

--
-- Дамп данных таблицы `source_site`
--

INSERT INTO `source_site` (`ss_id`, `ss_url`, `ss_dc_id`, `ss_descript`, `ss_dateadd`) VALUES
(1, 'http://pex8.com/', 68, 'Viatcheslav Moukhamediarov', '2017-09-18 07:11:20'),
(2, 'https://ukesa.com.ua/', 66, 'https://itrack.ru/whatcms/ Тренажор', '2017-09-18 07:14:22'),
(16, 'http://fishing.shimano.co.jp', 70, 'Мухамедьяров Катушки', '2017-10-05 13:22:06');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `dir_cms`
--
ALTER TABLE `dir_cms`
  ADD PRIMARY KEY (`dc_id`);

--
-- Индексы таблицы `dir_page_cms`
--
ALTER TABLE `dir_page_cms`
  ADD PRIMARY KEY (`dp_id`);

--
-- Индексы таблицы `dir_tags`
--
ALTER TABLE `dir_tags`
  ADD PRIMARY KEY (`dt_id`);

--
-- Индексы таблицы `pars_rule`
--
ALTER TABLE `pars_rule`
  ADD PRIMARY KEY (`pr_id`);

--
-- Индексы таблицы `result_data`
--
ALTER TABLE `result_data`
  ADD PRIMARY KEY (`rd_id`);

--
-- Индексы таблицы `result_img`
--
ALTER TABLE `result_img`
  ADD PRIMARY KEY (`ri_id`);

--
-- Индексы таблицы `source_page`
--
ALTER TABLE `source_page`
  ADD PRIMARY KEY (`sp_id`);

--
-- Индексы таблицы `source_site`
--
ALTER TABLE `source_site`
  ADD PRIMARY KEY (`ss_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `dir_cms`
--
ALTER TABLE `dir_cms`
  MODIFY `dc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;
--
-- AUTO_INCREMENT для таблицы `dir_page_cms`
--
ALTER TABLE `dir_page_cms`
  MODIFY `dp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT для таблицы `dir_tags`
--
ALTER TABLE `dir_tags`
  MODIFY `dt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT для таблицы `pars_rule`
--
ALTER TABLE `pars_rule`
  MODIFY `pr_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `result_data`
--
ALTER TABLE `result_data`
  MODIFY `rd_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `result_img`
--
ALTER TABLE `result_img`
  MODIFY `ri_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `source_page`
--
ALTER TABLE `source_page`
  MODIFY `sp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3889;
--
-- AUTO_INCREMENT для таблицы `source_site`
--
ALTER TABLE `source_site`
  MODIFY `ss_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
