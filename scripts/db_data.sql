insert into newcoupsmart.items (select * from dev_coupsmart.items where id = 2395);
insert into newcoupsmart.campaigns (select * from dev_coupsmart.campaigns where id = 2395);
insert into newcoupsmart.deals (select * from dev_coupsmart.deals where id = 1280);
insert into newcoupsmart.companies (select * from dev_coupsmart.companies where id = 1147);
insert into newcoupsmart.users_companies (select * from dev_coupsmart.users_companies  where companies_id = 1147);
insert into newcoupsmart.users (select * from dev_coupsmart.users where id = 459299);

insert into voucher_layouts (select vl.* from dev_coupsmart.voucher_layouts vl where vl.id >= 205);
insert into voucher_layout_parts (select * from dev_coupsmart.voucher_layout_parts where voucher_layout_id >= 205);