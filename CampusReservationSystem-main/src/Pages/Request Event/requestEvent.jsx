import React from 'react';
import './requestEvent.css';

const RequestVenueForm = () => {
  return (
    <div className="form-container">
      <h2>REQUEST VENUE</h2>

      <form>
        <div className="top-fields">
          <div>
            <label>REFERENCE NO.</label>
            <input type="text" />
          </div>
          <div>
            <label>DATE:</label>
            <input type="date" />
          </div>
        </div>

        <label>NAME OF REQUESTOR:</label>
        <input type="text" />

        <label>NAME OF DEPARTMENT / ORGANIZATION:</label>
        <input type="text" />

        <label>ACTIVITY:</label>
        <input type="text" />

        <label>PURPOSE:</label>
        <input type="text" />

        <label>NATURE OF ACTIVITY (PLEASE CHECK ONE)</label>
        <div className="checkbox-group">
          <label><input type="checkbox" /> CURRICULAR</label>
          <label><input type="checkbox" /> CO-CURRICULAR</label>
          <label><input type="checkbox" /> OTHERS</label>
          <input type="text" placeholder="(PLEASE SPECIFY)" />
        </div>

        <label>DATE/S NEEDED:</label>
        <div className="range-group">
          <label>FROM: <input type="date" /></label>
          <label>TO: <input type="date" /></label>
        </div>

        <label>TIME NEEDED:</label>
        <div className="range-group">
          <label>START: <input type="time" /></label>
          <label>END: <input type="time" /></label>
        </div>

        <label>PARTICIPANTS:</label>
        <input type="text" />

        <div className="pax-group">
          <label>NO. OF PAX:</label>
          <label>MALE: <input type="number" /></label>
          <label>FEMALE: <input type="number" /></label>
          <label>ESTIMATION: <input type="number" /></label>
        </div>

        <label>VENUE:</label>
        <select>
          <option value="">SELECT</option>
        </select>

        <label>LIST OF EQUIPMENT / MATERIALS NEEDED:</label>
        <div className="materials-group">
          <select>
            <option value="">SELECT</option>
          </select>
          <label>PCS.: <input type="number" /></label>
        </div>

        <button type="submit">SUBMIT</button>
      </form>
    </div>
  );
};

export default RequestVenueForm;
