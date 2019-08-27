
function appendFreezeHtml() {
	document.body.insertAdjacentHTML('beforeend', '<div class="freezing-wrap">' +
		'<div class="freezing-info">' +
		'<div class="freezing-info--title">Мы заморозили цену!</div>' +
		'<div class="freezing-info--price">1$ = <span class="dynamic-freezing-info--price"></span></div>' +
		'<div class="freezing-info--packages">Осталось <span class="packages-count">' + count_items_left +
		'</span> штук <br>по старому курсу</div>' +
		'<a href="#close" class="freezing-close"></a>' +
		'</div>' +

		'<div class="freezing-buyer">' +
		'<div class="freezing-buyer-prod-img">' +
		'<!-- <img src="product" alt=""> -->' +
		'</div>' +
		'<div class="freezing-buyer-info">' +
		'<div class="dynamic-info">' +
		'<span class="freezing-buyer--name"></span>,' +
		' г. <span class="freezing-buyer--town"></span>' +
		'<span class="freezing-buyer--flag"></span>' +
		'</div>' +
		'Только что <span class="freez_oformila">оформил</span> заказ.' +
		'</div>' +
		'</div>' +
		'</div>');
}

function addOnCloseFreezeEvent() {
	$('.freezing-close').on('click', function (e) {
		e.preventDefault();
		$('.freezing-wrap').addClass('freezing-hide');
	});
}

var buyers = {
	'RU': {
		img: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAa0lEQVR42u3QwQkAMAgAMfdf2hYc4yIc6NPM7k659PMAAAAAAAAAAAAAAFy1AQAAAAAAAAAAAAAAAIC/zF2VAAAAAAAAAAAAAAAAAODqCQAAAAAAAAAAAAAAAAAAgGwAAAAAAAAAAAAAAER72yqN3ef7lPIAAAAASUVORK5CYII=',
		town: ["Москва", "Воронеж", "Санкт-Петербург", "Новосибирск", "Екатеринбург", "Нижний Новгород", "Казань", "Челябинск", "Омск", "Самара", "Ростов-на-Дону", "Уфа", "Красноярск", "Пермь", "Волгоград", "Саратов", "Краснодар", "Тольятти", "Тюмень", "Ижевск", "Хабаровск", "Владивосток", "Оренбург", "Пенза"],
		people_name: {
			man: ["Сергей", "Леша", "Алексей", "Николай", "Александр", "Павел", "Паша", "Дима", "Дмитрий", "Женя", "Евгений", "Слава", "Святослав", "Андрей", "Антон", "Артем", "Геннадий", "Макс", "Максим", "Олег", "Виктор", "Роман", "Рома", "Денис", "Руслан", "Вадим", "Костя", "Влад", "Владислав", "Марат", "Юрий"],
			woman: ['Татьяна', 'Лиза', 'Алла', 'Наталья', 'Ксения', 'Оксана', 'Юлия', 'Ольга', 'Екатерина', 'Руслана', 'Любовь']
		},
		cursePrice: '45 рублей'
	},
	'UA': {
		town: ["Киев", "Харьков", "Одесса", "Днепропетровск", "Донецк", "Запорожье", "Львов", "Кривой Рог", "Николаев", "Мариуполь", "Луганск", "Винница", "Макеевка", "Херсон", "Полтава", "Чернигов", "Черкассы", "Житомир", "Сумы", "Хмельницкий", "Черновцы", "Ровно", "Днепродзержинск", "Кировоград", "Ивано-Франковск", "Кременчуг", "Тернополь", "Луцк", "Белая Церковь", "Краматорск"],
		people_name: {
			woman: ['Татьяна', 'Лиза', 'Алла', 'Наталья', 'Ксения', 'Оксана', 'Юлия', 'Ольга', 'Екатерина', 'Руслана', 'Любовь'],
			man: ["Сергей", "Леша", "Алексей", "Николай", "Александр", "Павел", "Паша", "Дима", "Дмитрий", "Женя", "Евгений", "Слава", "Святослав", "Андрей", "Антон", "Артем", "Геннадий", "Макс", "Максим", "Олег", "Виктор", "Роман", "Рома", "Денис", "Руслан", "Вадим", "Костя", "Влад", "Владислав", "Марат", "Юрий"]
		},
		img: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAcUlEQVR42u3aMQHAMADDsKAYf0KFUCC7UiDRYQK6nbZZLgAAAAAAAAAAAAAAjAMkp0sBAAAAAAAAAAAAAAAAAAAAAAAAALAO8N+vSwEAAAAAAAAAAAAAAAAAAAAAAAAAsA7gFAUAAAAAAAAAAAAAYKoHyOHoWIZhLHEAAAAASUVORK5CYII=',
		cursePrice: '15 грн.'
	},
	'BY': {
		town: ["Бобруйск", "Борисов", "Витебск", "Гомель", "Минск", "Могилёв", "Мозырь", "Орша", "Полоцк", "Речица", "Слуцк", "Быхов", "Ветка", "Горки", "Городок", "Дзержинск", "Добруш", "Дрисса", "Жлобин", "Калинковичи", "Климовичи", "Мстиславль", "Осиповичи", "Старые Дороги", "Старые Чериков"],
		people_name: {
			man: ["Сергей", "Леша", "Алексей", "Николай", "Александр", "Павел", "Паша", "Дима", "Дмитрий", "Женя", "Евгений", "Слава", "Святослав", "Андрей", "Антон", "Артем", "Геннадий", "Макс", "Максим", "Олег", "Виктор", "Роман", "Рома", "Денис", "Руслан", "Вадим", "Костя", "Влад", "Владислав", "Марат", "Юрий"],
			woman: ['Татьяна', 'Лиза', 'Алла', 'Наталья', 'Ксения', 'Оксана', 'Юлия', 'Ольга', 'Екатерина', 'Руслана', 'Любовь']

		},
		img: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAACGklEQVR42u2avUoFMRCF90Hs7Ozs7CxtbG1sbWxtbC3sBUtBsBfsxQcQC99Cy6v4h38g0S9wJIQUZrG5Z+dCyMzscmFOMiczsxlSSsOUxxAABAABQAAQAAQAAUAAEAAEAAFA+rq5zYPfbGEx/cV2/fMX8ziaALweHKaXvf30vLObnWVGb9l41w4Afu+nZ9lRHGRGb9n0swKAbf2wtp4eNzbT5+VVntFbNssQYGXvlpbzjKPSWzbtArsQIMbvV1bzajOjt2yWIUCMP21tZydZbWb0ls2SBFlhVrckO+2AkgjRedeSBHESksNRZvSWzZIEFeM4Kbkc2Hgm2XIHEOfEOKtMwoPOQMbGM3TbTJD4xkGcZRYH1DZLEmRVlfJqCIDaZp0IyVFiXYkQsuy2iZCIT44q7mvZlgSV68vR8iRAll11gXU5/HZ8kmWFADI263JYGaBS4poElQLrHdtyWDyAw2qICBAlSranAM7KSYFR67xjewqUZ37dECnzANtTQCuuAkgcoCKoPBVsSfDj/OJXLkkQG89sSbCsBXTclcdg+cy2ITLpRChS4QqAuiHSAmA4GuZy/Fs5bAXAmIaI3Q4o218UP2qJIZdtMssdMKYparcDetvidjug98OIHQn2fhqzAmDMx1ErAMZ8Hrcjwd4LEnYh0HtFxo4Eey9J2YVA7zU5JwDipmgAEAAEAAFAABAABAABQAAQAExnfAPZWbMXfCnZcgAAAABJRU5ErkJggg==',
		cursePrice: '14 бел.руб.'
	},
	'MD': {
		town: ["Кишинёв", "Тирасполь", "Бельцы", "Бендеры", "Рыбница", "Кагул", "Унгень", "Сороки", "Оргеев", "Дубоссары"],
		people_name: {
			man: ["Сергей", "Леша", "Алексей", "Николай", "Александр", "Павел", "Паша", "Дима", "Дмитрий", "Женя"],
			woman: ['Татьяна', 'Лиза', 'Алла', 'Наталья', 'Ксения', 'Оксана', 'Юлия', 'Ольга', 'Екатерина', 'Руслана', 'Любовь']

		},
		img: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAADUklEQVR42u3Yz4sbZRzH8ffzzK9k08Qka7a1u1Sb0hW6qBURwR+VHqwiRdiTlV56EBYp6MWbJw9VqPbiLwShBxVXagWRorWHqlRF2RbFQtFKu2Kr1jSb3c0mm8lkZh4Po/9BGGXyfeADw8zAMK/5zneeebQxhlGOZsSHAAiAAAiAAAiAAAiAAIw8gFInzDDjX1RDzdV6ZaiRChAAARAAARAAARAAARCA/2AowLJA62R7ZACUSjKI4GoDuj3QVrLv3+OZBVAKlE6eupuDH77XvHCoxjvz4wQDcJzkPNvOIIBSyZWOvjvGa28VwcBdd8YUCxto/FnAdmB1DQ4dKXPilIvjZgzAceDSouLDj4t88lmR099qqhNwZaVPO+6iCnD8U4cvzxSZP76B1XbSIzIDoBX4Pix3ArQTsHjyRk6+Uqbei6lc05x6qULzXIXQ6rHSiQjD9JpjKgDGQN6DqVqebZvz7Gq4WGcd6m6PLd0Q+6zNnp7HzVN5NlU9tMrYKxDH8NNvNuMlj4miR2AFeDvX0A2XXN9C39YmimM2lj3Gb3DxAwijdKpAp9EAwxjmP3dodkOCSLEwUeVra5x73mgx9fwaX/UnOL+phBlAGBrO/QxBmM5nUadR/p4L994x4JfWOpFWHKnu59UrB/liscZHF6Y53HqOo5VHcQmIga2TUMhBbDLUA3btCBm4EZfaMQ/WvmPvtg9YvT5AdVrc5P1IjpA4gnxpnS0b07n59CZCBlxbsXksD4Um95cXWO5Pc20wRZNbuLXc4emd72OXeljY+H4GZ4IahYvFXw2DfabN4YePEf3qsDVY4uXdb+N9s0Jtss3SkpPqP0JqAN2+YXZPg9mKT313wIXLY/y+cJ3zp9dZc1zsSUOxBTtuXyIIMlYBwQC212OefCLgqRf7XOzCe8cKlCsWlguvv1kiPzPgwLN95vb3KZcgijI4ETIGHAseui9mdrbJcsfH8tbZ93iLmbpBAbZOps4ma6+AMUnCEGpVw4F9MXNzf/DMwSaPPRJTGEueevzPeZleEAlD8LvwwN0wsz3ZjuMRXBLz+0l/GLklsf/TEAABEAABEAABEAABEABj9qphJjdthpqpy8tDjVSAAAiAAAiAAAiAAAiAAIz4+Bv02cLf9cqd5AAAAABJRU5ErkJggg==',
		cursePrice: '14 лей'
	},
	'AM': {
		town: ["Ереван", "Гюмри", "Ванадзор", "Вагаршапат", "Раздан", "Абовян", "Капан", "Армавир", "Гавар", "Арташат"],
		people_name: {
			man: ["Давид", "Нарек", "Алекс", "Гор", "Тигран", "Гайк", "Арман", "Артур", "Эрик", "Ален"],
			woman: ['Наре', 'Мари', 'Милена', 'Мария', 'Анаит', 'Анна', 'Ева', 'Натали', 'Лилит', 'Гоар']
		},
		img: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAcElEQVR42u3aMQEAIADDsPl3gAVMjgMZy1EDuZu2WS4AAAAAAAAAAAAAAKwDJJ0KAAAAAAAAAAAAAAAAANomuV0KAAAAAAAAAAAAAAAAAH4nnQoAAAAAAAAAAAAAAAAAABglAQAAAAAAAAAAAABY6wFqxW624E1aHwAAAABJRU5ErkJggg==',
		cursePrice: '335 драм'
	},
	'AZ': {
		town: ["Баку", "Гянджа", "Сумгаит", "Мингечаур", "Хырдалан", "Нахичевань", "Шеки", "Евлах", "Степанакерт", "Ленкорань"],
		people_name: {
			man: ["Айхан", "Али", "Гусейн", "Мурад", "Мурад", "Мухаммед", "Ровшан", "Рузи", "Теймур", "Хагани"],
			woman: ['Адиля', 'Аида', 'Мехри', 'Ламия', 'Наргиз', 'Роза', 'Сара', 'Тарай', 'Фарах', 'Чинара']
		},
		img: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAACuklEQVR42u3aTWgTURAH8NfGKnrQg4igeJSU2tQ2JrGxVdFWKIiX+o1FrKhoDoIHqZce/AA9WEEUVFBQghehPVXxUhUiCIoIgvZSa5EWm83HmsY0H93k77y3NTbaKKI9bDKBYcnjLcn8MjO8hQgAopxDMAADMAADMAADMAADMAADlDmAuBlAOQUDMAADMAADMAADMAADMAADMICKEeHCv0Zw3vrZw0Yh1mJc1NKV9lXJ9w303oFgxW/uKxL/47vOCYBGX+6XqHRTsg5EvYeQvNUL4+0QjMERpPqeIu67iPDyVrVn1nuLhHUAVPL1SJy/DWSz8kORfvwCsX1diDZ1Qm/1Qd96AqHFm0wEm6e0AIJU8l+7b+D7a8J3Sa2ZUa/aQhPrKHmPmby8Vrj/WBGWAJA9HrG3I5dMq+Qnrz+gterC5GTSC5tUBUTsOxHr6KY1J0JLW+jqtjrAGsRPX1XJS4RI9S7zF5+ZDAHItcRlPzKBNzA+jNJseILwqu1UGQ1WB3AgebdfAUwNfoS2wGuW98w9Nheirv3IDLzMt0k2GkNsTxdVhrfoTLAOwL1pgPfD0OYXAWjsQObZ60KAg9QKizZYHYBa4Mw1swUmUwivbleDr7AF5L46JK7cR+bVOxifPiP98DlCK9rUDLH+EKzZjVx6SiHIJMeF3Zz0BUOwmYbeFkRq92Li8DlqFQ9Cy7ZZfwiav64DiQt3zNrO5RDrPEtrNWpdAqmKEE6zNWzT99HJ8OdWsfZBqNKJRI8/3+Op3gF82XESUecB6BuPQN98DNqSEj0I/TgN1kFvOY6k/xGMoVEYw2NI9wcQP9WD8Mo22uMq0aNwftp7FIJ8+NGqGmnCN6uHIvUwJP4u+TkDEH1HUU7BAAzAAAzAAAzAAAzAAAzAAAzAf5RkAAZgAAZgAAZgAAZgAAYov/gGmkqMvEVo3j0AAAAASUVORK5CYII=',
		cursePrice: '1 манат'
	},
	'KZ': {
		town: ["Астана", "Алматы", "Шымкент", "Усть-Каменогорск", "Кызылорада", "Тараз", "Талдыкурган", "Атырау", "Актобе", "Актау", "Уральск", "Петропавлоск", "Костанай", "Кокшетау", "Павлодар", "Жезказган", "Темиртау", "Семей", "Жанаозен", "Кульсары", "Экибастуз", "Экибастуз", "Туркестан", "Жетысай", "Сарыагаш", "Аксай", "Байконур", "Балхаш", "Сатпаев", "Рудный"],
		people_name: {
			man: ["Андрей", "Анвар", "Иван", "Асмет", "Аскат", "Бакир", "Гани", "Ерасыл", "Ермек", "Жандос", "Жунис", "Илияс", "Карим", "Касиман", "Мурат", "Мухит", "Нурсултан", "Рахман", "Санжар", "Султан", "Шади", "Ыскак"],
			woman: ['Абиба', 'Айгуль', 'Бадинур', 'Гульмира', 'Жанат', 'Зара', 'Инжу', 'Кулай', 'Кулира', 'Нурша']
		},
		img: 'data:image/png;base64,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',
		cursePrice: '188 тенге'
	},
	'KG': {
		town: ["Бишкек", "Ош", "Ак-Суу", "Баткен", "Балыкчы", "Джалал-Абад", "Исфана", "Кербен", "Кант", "Кара-Суу"],
		people_name: {
			man: ["Азамат", "Александр", "Нурбек", "Нурлан", "Айбек", "Руслан", "Сергей", "Владимир", "Кубанычбек", "Мирлан"],
			woman: ['Абиба', 'Айгуль', 'Бадинур', 'Гульмира', 'Жанат', 'Зара', 'Инжу', 'Кулай', 'Кулира', 'Нурша']
		},
		img: 'data:image/png;base64,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',
		cursePrice: '48 сом'
	},
	'EN': {
		town: ["Лондон", "Бирмингем", "Лидс", "Шеффилд", "Манчестер", "Ливерпуль", "Бристоль", "Ковентри", "Брадфорд", "Ноттингем"],
		people_name: {
			man: ["Оливер", "Джек", "Гарри", "Джейкоб", "Чарли", "Томас", "Оскар", "Уильям", "Джеймс", "Джордж"],
			woman: ['Эбби', 'Анжела', 'Аманда', 'Одри', 'Шэрил', 'Синди', 'Дана', 'Эрика', 'Фиона', 'Клэр']
		},
		img: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAcklEQVR42u3asQ0AMAgDQe+/NNkg6SjiQ/oeXUVBZibNBQAAAAAAAAAAAAAAADuT3NsbAAAAAAAAAAAAAAAAAAAAAMBrsd8CAAAAAAAAADiEAAAAAAAAAAAAAAAAAAAAgG4An6IAAAAAAAAAAAAAAPR0AIQsb17RbOTRAAAAAElFTkSuQmCC',
		cursePrice: '0.6 Евро'
	},
	'LT': {
		town: ["Вильнюс", "Каунас", "Паланга", "Клайпеда", "Неринга", "Бирштонас", "Друскининкай", "Паневежис", "Шяуляй", "Тракай", "Зарасай"],
		people_name: {
			man: ["Лукас", "Йокубас", "Каюс", "Йонас", "Габриэлюс", "Бенас", "Домантас", "Матас", "Ноюс", "Мантас"],
			woman: ['Аустея', 'Габия', 'Ева', 'Сауле', 'Тома', 'Камилия', 'Юрта', 'Оксана', 'Ольга', 'Иева']
		},
		img: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAe0lEQVR42u3aMQ2AUBRD0ecBEUy4QA0OGb8wUhKQ0TPcpPOZO0mmuQEAAAAAAAAAAAAAoBzgubc0BQAAAAAAAAAAAAAAAAD/uM40BQAAAAAAAAAAAAAAAABfaz/SFAAAAAAAAAAAAAAAAAAAcJQEAAAAAAAAAAAAAKCuF/ipNJVP4JWrAAAAAElFTkSuQmCC',
		cursePrice: '2 лит'
	},
	'LV': {
		town: ["Рига", "Юрмала", "Даугавпилс", "Лиепая", "Елгава", "Вентспилс", "Екабпилс", "Цесис", "Валмиера", "Резекне"],
		people_name: {
			man: ["Янис", "Каспар", "Рейнис", "Роберт", "Карлис", "Айвар", "Рихард", "Ричард", "Донат", "Харальд", "Ивар", "Илмар"],
			woman: ['Эмилия', 'Ева', 'Камила', 'Юрте', 'Иева', 'Татьяна', 'Ольга', 'Марина', 'Оксана', 'Юлия']
		},
		img: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAcUlEQVR42u3aQQ0AMAwDsUDac3wHNAMSVzoCflVq0zbLBQAAAAAAAAAAAACAcYB3bpcCAAAAAAAAAAAAAAAAAAAAADTdGwAAAAAAAACARQgAAAAAAAAAAAAAAAAAAABYvgv4FAUAAAAAAAAAAAAAYKcPE3SSF47xF50AAAAASUVORK5CYII=',
		cursePrice: '0.5 лит'
	},
	'DE': {
		town: ["Берлин", "Гамбург", "Мюнхен", "Кёльн", "Франкфурт-на-Майне", "Штутгарт", "Дюссельдорф", "Дортмунд", "Эссен", "Бремен"],
		people_name: {
			man: ["Петер", "Михаэль", "Томас", "Андреас", "Вольфганг", "Клаус", "Юрген", "Гюнтер", "Штефан", "Кристиан", "Уве"],
			woman: ['Миа', 'Эмма', 'Ханна', 'Леа', 'Софи', 'Анна', 'Леона', 'Линни', 'Мари', 'Нэля']
		},
		img: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAb0lEQVR42u3aMQHAQAzEsOOPrZz8Q2FEgwlo9qpdbgAAAAAAAAAAAAAAHAfY1qUAAAAAAAAAAAAAAAAA4G/rVAAAAAAAAAAAAAAAAABQrW+dCgAAAAAAAAAAAAAAAAAAGCUBAAAAAAAAAAAAALjWA6kBEAfC9SDsAAAAAElFTkSuQmCC',
		cursePrice: '0.6 Евро'
	},
	'GE': {
		town: ["Тбилиси", "Кутаиси", "Батуми", "Рустави", "Зугдиди", "Сенаки", "Гори", "Поти", "Самтредиа", "Хашури"],
		people_name: {
			man: ["Георги", "Давити", "Зураби", "Александрэ", "Михаили", "Тамази", "Иракли", "Нодари", "Гурами", "Владимири"],
			woman: ['Нина', 'Тамара', 'Шушаника', 'Кетевань', 'Нанара', 'Натела', 'Нана', 'Ламара', 'Нино', 'Теа']
		},
		img: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAxUlEQVR42u3auwnDMBAAUK3gJbyIVtDQbuxag7i/dCa4SIIsbIie4GodT7/jUIqINHIkAAAAAAAAAAAAAAAA3DNS+hz3DQAAADQC7MsaNZeoucS+rI8BXMrj6g7Ypjm2aX58BzTn0QLwrnye+OcV6ADQJY8WgJrLMeE5ai63AXTJA4Aj4BL0DCqEAAAAcAB8S+zfAgAAAAAAAACgEAIAAICGiJaYpqi2OABHwCXoGVQIAQAwNoCfogAAAAAAAAAAAAAAjBMvBbGRj1pEQwIAAAAASUVORK5CYII=',
		cursePrice: '2 лари'
	}
};

var initFreeze = function () {

	var first_geo_select = $('[name=target_geo_hash]').first();
	var first_geo_select_val = first_geo_select.val();
	var country_code = first_geo_select.find('option[value=' + first_geo_select_val + ']').data('country_code');


	if (buyers[country_code] === undefined) {
		return false;
	}

	appendFreezeHtml();
	addOnCloseFreezeEvent();

	$('span.dynamic-freezing-info--price').text(buyers[country_code].cursePrice);

	buyItem(country_code);

	setTimeout(function () {
		$('.freezing-wrap').addClass('freezing-active');
	}, 2000);

};

var buyItem = function (country_code) {
	var timeInterval = setTimeout(function tInterval() {

		var lengthTown = buyers[country_code].town.length;

		if (typeof woman !== 'undefined' && woman == 1) {
			var id = 'woman';
			$("span.freez_oformila").text('оформила');
		}
		else {
			var id = 'man';
		}
		var lengthName = buyers[country_code].people_name[id].length;

		var buyerName = getRandomInt(0, lengthName - 1);
		var buyerTown = getRandomInt(0, lengthTown - 1);

		count_items_left--;

		$('.freezing-buyer--name').text(buyers[country_code].people_name[id][buyerName]);
		$('.freezing-buyer--town').text(buyers[country_code].town[buyerTown]);
		//$('.freezing-buyer--flag').removeClass().addClass('new-flag');
		$('.freezing-buyer--flag').attr('style', 'background: url(\'' + buyers[country_code].img + '\'); background-size: cover; margin-top: -5px; margin-left: 5px;');
		//$('.freezing-buyer--flag').removeClass().addClass("freezing-buyer--flag " + arr_buyer.cnr.class_flag[0]);
		$('.packages-count').text(count_items_left);

		$('.freezing-buyer').addClass('freezing-buyer--show');

		setTimeout(function () {
			$('.freezing-buyer').removeClass('freezing-buyer--show');
		}, 5000);

		var randInterval = getRandomInt(12, 19);

		if (count_items_left > 4) {
			timeInterval = setTimeout(tInterval, randInterval * 1000);
		}

	}, 8000);
};


//initFreeze();


function getRandomInt(min, max) {
	return Math.floor(Math.random() * (max - min + 1)) + min;
}