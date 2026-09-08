/**
 * Ethiopian Calendar Client-Side Engine
 * Fast, accurate GMT+3 East Africa Time bound arithmetic
 */

const EthiopianCalendarJS = (() => {
  const MONTHS = [
    { id: 1, am: 'መስከረም', en: 'Meskerem' },
    { id: 2, am: 'ጥቅምት', en: 'Tikimt' },
    { id: 3, am: 'ኅዳር', en: 'Hidar' },
    { id: 4, am: 'ታኅሣሥ', en: 'Tahsas' },
    { id: 5, am: 'ጥር', en: 'Tir' },
    { id: 6, am: 'የካቲት', en: 'Yekatit' },
    { id: 7, am: 'መጋቢት', en: 'Megabit' },
    { id: 8, am: 'ሚያዝያ', en: 'Miyazya' },
    { id: 9, am: 'ግንቦት', en: 'Ginbot' },
    { id: 10, am: 'ሰኔ', en: 'Sene' },
    { id: 11, am: 'ሐምሌ', en: 'Hamle' },
    { id: 12, am: 'ነሐሴ', en: 'Nehase' },
    { id: 13, am: 'ጳጉሜ', en: 'Pagume' }
  ];

  const WEEKDAYS = [
    { id: 0, am: 'እሑድ', en: 'Sunday' },
    { id: 1, am: 'ሰኞ', en: 'Monday' },
    { id: 2, am: 'ማክሰኞ', en: 'Tuesday' },
    { id: 3, am: 'ረቡዕ', en: 'Wednesday' },
    { id: 4, am: 'ሐሙስ', en: 'Thursday' },
    { id: 5, am: 'ዓርብ', en: 'Friday' },
    { id: 6, am: 'ቅዳሜ', en: 'Saturday' }
  ];

  function getEATComponents(input) {
    let base = input ? new Date(input) : new Date();
    if (isNaN(base.getTime())) base = new Date();

    const utcMs = base.getTime() + base.getTimezoneOffset() * 60000;
    const eatDate = new Date(utcMs + 3 * 3600 * 1000);

    return {
      gYear: eatDate.getFullYear(),
      gMonth: eatDate.getMonth() + 1,
      gDay: eatDate.getDate(),
      dayOfWeek: eatDate.getDay(),
      hours: eatDate.getHours(),
      minutes: eatDate.getMinutes(),
      seconds: eatDate.getSeconds()
    };
  }

  function toEthiopianDate(input) {
    const { gYear, gMonth, gDay, dayOfWeek, hours, minutes, seconds } = getEATComponents(input);

    const a = Math.floor((14 - gMonth) / 12);
    const y = gYear + 4800 - a;
    const m = gMonth + 12 * a - 3;
    const jdn = gDay + Math.floor((153 * m + 2) / 5) + 365 * y + Math.floor(y / 4) - Math.floor(y / 100) + Math.floor(y / 400) - 32045;

    const ethJdnOffset = 1723856;
    const daysSinceEpoch = jdn - ethJdnOffset;
    const ethEra = Math.floor(daysSinceEpoch / 1461);
    const remDaysInEra = daysSinceEpoch % 1461;
    const ethYearInEra = Math.min(Math.floor(remDaysInEra / 365), 3);
    const dayOfYear = remDaysInEra - ethYearInEra * 365;

    const ethYear = ethEra * 4 + ethYearInEra;
    const ethMonth = Math.floor(dayOfYear / 30) + 1;
    const ethDay = (dayOfYear % 30) + 1;

    const monthObj = MONTHS[ethMonth - 1] || MONTHS[0];
    const weekdayObj = WEEKDAYS[dayOfWeek] || WEEKDAYS[0];

    const pad = (n) => (n < 10 ? '0' + n : n);
    const ampm = hours >= 12 ? 'PM' : 'AM';
    const displayHours = hours % 12 || 12;

    const ethHour = (hours + 6) % 12 || 12;
    let ethPeriodAm = 'ጠዋት';
    let ethPeriodEn = 'Morning';

    if (hours >= 6 && hours < 12) {
      ethPeriodAm = 'ጠዋት'; ethPeriodEn = 'Morning';
    } else if (hours >= 12 && hours < 18) {
      ethPeriodAm = 'ቀን'; ethPeriodEn = 'Afternoon';
    } else if (hours >= 18 && hours < 24) {
      ethPeriodAm = 'ምሽት'; ethPeriodEn = 'Evening';
    } else {
      ethPeriodAm = 'ሌሊት'; ethPeriodEn = 'Night';
    }

    return {
      year: ethYear,
      month: ethMonth,
      day: ethDay,
      monthNameAm: monthObj.am,
      monthNameEn: monthObj.en,
      weekdayAm: weekdayObj.am,
      weekdayEn: weekdayObj.en,
      formattedAm: `${monthObj.am} ${ethDay}, ${ethYear} ዓ.ም`,
      formattedEn: `${monthObj.en} ${ethDay}, ${ethYear} EC`,
      timeAm: `${pad(displayHours)}:${pad(minutes)}:${pad(seconds)} ${ampm === 'AM' ? 'ጠዋት' : 'ከሰዓት'}`,
      timeEn: `${pad(displayHours)}:${pad(minutes)}:${pad(seconds)} ${ampm}`,
      traditionalTimeAm: `${ethHour}:${pad(minutes)} ${ethPeriodAm}`,
      traditionalTimeEn: `${ethHour}:${pad(minutes)} ${ethPeriodEn}`,
      isPagume: ethMonth === 13
    };
  }

  function startLiveClock(elementIdAm, elementIdEn) {
    function update() {
      const eth = toEthiopianDate();
      const elAm = document.getElementById(elementIdAm);
      const elEn = document.getElementById(elementIdEn);
      if (elAm) elAm.textContent = `${eth.weekdayAm}፣ ${eth.formattedAm} (${eth.traditionalTimeAm})`;
      if (elEn) elEn.textContent = `${eth.weekdayEn}, ${eth.formattedEn} (${eth.traditionalTimeEn})`;
    }
    update();
    setInterval(update, 1000);
  }

  return {
    toEthiopianDate,
    formatDate: (inp, lang = 'am') => {
      if (!inp) return '—';
      const r = toEthiopianDate(inp);
      return lang === 'am' ? r.formattedAm : r.formattedEn;
    },
    formatDateTime: (inp, lang = 'am') => {
      if (!inp) return '—';
      const r = toEthiopianDate(inp);
      return lang === 'am' ? `${r.formattedAm} (${r.traditionalTimeAm})` : `${r.formattedEn} (${r.traditionalTimeEn})`;
    },
    startLiveClock
  };
})();
