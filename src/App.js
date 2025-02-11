import React, { useState } from "react";

const App = () => {
  const [count, setCount] = useState(0);

  return (
    <div className="container text-center mt-5">
      <h2>Counters</h2>
      <h1>{count}</h1>
      <button className="btn btn-danger mx-2" onClick={() => setCount(count - 1)}>
        -
      </button>
      <button className="btn btn-success mx-2" onClick={() => setCount(count + 1)}>
        +
      </button>      
    </div>
  );
};

export default App;