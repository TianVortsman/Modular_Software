// timeEngine.js

class TimeEntry {}

class PayPeriod {
    constructor({ type = 'monthly', startDay = 1, durationDays = null, startDate = null }) {
      this.type = type;
      this.startDay = startDay;
      this.durationDays = durationDays;
      this.startDate = startDate ? new Date(startDate) : new Date();
    }
  
    getPeriodRange() {
      const start = new Date(this.startDate);
      let end;
  
      if (this.type === 'monthly') {
        end = new Date(start);
        end.setMonth(start.getMonth() + 1);
        end.setDate(this.startDay - 1 || 0);
      }
  
      else if (this.type === 'weekly') {
        end = new Date(start);
        end.setDate(start.getDate() + 6);
      }
  
      else if (this.type === 'biweekly' || this.type === 'other') {
        end = new Date(start);
        end.setDate(start.getDate() + (this.durationDays || 14) - 1);
      }
  
      return { start, end };
    }
  }
  
  class Groupings {
    constructor(data = {}) {
      this.division = data.division || "General";
      this.department = data.department || "Unassigned";
      this.group = data.group || null;
      this.costCentre = data.costCentre || null;
      this.reportingManager = data.reportingManager || null;
      this.supervisor = data.supervisor || null;
      this.site = data.site || "Head Office";
      this.jobTitle = data.jobTitle || "Employee";
      this.hourlyRate = data.hourlyRate || 0;
    }
  }
  
class Holiday {}

class BasicTimeCalculator {
    constructor(clockings = []) {
      this.clockings = clockings.sort((a, b) => new Date(a.datetime) - new Date(b.datetime));
    }
  
    calculateWorkedTime() {
      let totalMinutes = 0;
  
      for (let i = 0; i < this.clockings.length - 1; i += 2) {
        const inTime = new Date(this.clockings[i].datetime);
        const outTime = new Date(this.clockings[i + 1].datetime);
  
        const duration = (outTime - inTime) / 60000; // Convert ms to minutes
        totalMinutes += duration;
      }
  
      return totalMinutes;
    }
  }
  
class OvertimeProfile {}

class RoundingProfile {}

class AbsenteeismProfile {}

class DayShift {}

class NightShift {}

class Rules {}

class EmpRules {}

class Approval {}
