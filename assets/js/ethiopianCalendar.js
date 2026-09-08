/**
 * Ethiopian Calendar Conversion Library (GMT+3 / East Africa Time)
 * Standalone JavaScript implementation matching original TypeScript utility.
 */

(function(global) {
  const ETHIOPIAN_MONTHS = [
    { am: 'መስከረም', en: 'Meskerem' },
    { am: 'ጥቅምት', en: 'Tikimt' },
    { am: 'ኅዳር', en: 'Hidar' },
    { am: 'ታኅሣሥ', en: 'Tahsas' },
    { am: 'ጥር', en: 'Tir' },
    { am: 'የካቲት', en: 'Yekatit' },
    { am: 'መጋቢት', en: 'Megabit' },
    { am: 'ሚያዝያ', en: 'Miyazya' },
    { am: 'ግንቦት', en: 'Ginbot' },
    { am: 'ሰኔ', en: 'Sene' },
    { am: 'ሐምሌ', en: 'Hamle' },
    { am: 'ነሐሴ', en: 'Nehase' },
    { am: 'ጳጉሜ', en: 'Pagume' }
  ];

  const ETHIOPIAN_WEEKDAYS = [
    { am: 'እሑድ', en: 'Sunday' },
    { am: 'ሰኞ', en: 'Monday' },
    { am: 'ማክሰኞ', en: 'Tuesday' },
    { am: 'ረቡዕ', en: 'Wednesday' },
    { am: 'ሐሙስ', en: 'Thursday' },
    { am: 'ዓርብ', en: 'Friday' },
    { am: 'ቅዳሜ', en: 'Saturday' }
  ];

  function toEthiopianDate(gregorianDateInput) {
    let gDate;
    if (!gregorianDateInput) {
      gDate = new Date();
    } else if (gregorianDateInput instanceof Date) {
      gDate = gregorianDateInput;
    } else if (typeof gregorianDateInput === 'number') {
      gDate = new Date(gregorianDateInput);
    } else {
      gDate = new Date(String(gregorianDateInput));
    }

    if (isNaN(gDate.getTime())) {
      gDate = new Date();
    }

    // Force GMT+3 East Africa Time
    const utcTime = gDate.getTime() + (gDate.getTimezoneOffset() * 60000);
    const eatDate = new Date(utcTime + (3600000 * 3));

    const gYear = eatDate.getFullYear();
    const gMonth = eatDate.getMonth() + 1;
    const gDay = eatDate.getDate();
    const dayOfWeek = eatDate.getDay();
    const hours = eatDate.getHours();
    const minutes = eatDate.getMinutes();
    const seconds = eatDate.getSeconds();

    // JDN Conversion
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

    const monthData = ETHIOPIAN_MONTHS[ethMonth - 1] || ETHIOPIAN_MONTHS[0];
    const weekdayData = ETHIOPIAN_WEEKDAYS[dayOfWeek] || ETHIOPIAN_WEEKDAYS[0];

    const pad = (n) => String(n).padStart(2, '0');
    const displayHours = hours % 12 || 12;
    const ampm = hours >= 12 ? 'PM' : 'AM';

    return {
      year: ethYear,
      month: ethMonth,
      day: ethDay,
      monthNameAm: monthData.am,
      monthNameEn: monthData.en,
      weekdayAm: weekdayData.am,
      weekdayEn: weekdayData.en,
      formattedAm: `${weekdayData.am}፣ ${monthData.am} ${ethDay} ቀን ${ethYear} ዓ.ም`,
      formattedEn: `${weekdayData.en}, ${monthData.en} ${ethDay}, ${ethYear} E.C.`,
      timeAm: `${pad(displayHours)}:${pad(minutes)}:${pad(seconds)} ${ampm === 'AM' ? 'ጠዋት' : 'ከሰዓት'}`,
      timeEn: `${pad(displayHours)}:${pad(minutes)}:${pad(seconds)} ${ampm} (EAT)`
    };
  }

  global.EthiopianCalendar = {
    toEthiopianDate,
    months: ETHIOPIAN_MONTHS,
    weekdays: ETHIOPIAN_WEEKDAYS
  };
})(window);
