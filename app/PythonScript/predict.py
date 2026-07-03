import sys
import os
import json
import warnings

# Suppress sklearn/numpy version warnings
warnings.filterwarnings('ignore')

try:
    import numpy as np
    import joblib
except ImportError as e:
    print(json.dumps({
        "error": f"Missing required python package: {str(e)}",
        "fallback_needed": True
    }))
    sys.exit(1)

def main():
    # Check arguments: predict.py <ph_value> <turbidity_value> <tds_value> <temperature_value>
    if len(sys.argv) < 5:
        print(json.dumps({
            "error": "Missing input arguments. Usage: predict.py <ph> <turbidity> <tds> <temp>",
            "fallback_needed": True
        }))
        sys.exit(1)

    try:
        ph = float(sys.argv[1])
        turbidity = float(sys.argv[2])
        tds = float(sys.argv[3])
        temp = float(sys.argv[4])
    except ValueError as e:
        print(json.dumps({
            "error": f"Invalid input argument type: {str(e)}",
            "fallback_needed": True
        }))
        sys.exit(1)

    # Models directory
    base_dir = os.path.dirname(os.path.abspath(__file__))
    
    model_paths = {
        "hcn_air": os.path.join(base_dir, "model_hcn_air_gadungguard.pkl"),
        "hcn_umbi": os.path.join(base_dir, "model_hcn_umbi_gadungguard.pkl"),
        "status_air": os.path.join(base_dir, "model_status_air_gadungguard.pkl"),
        "status_gadung": os.path.join(base_dir, "model_status_gadung_gadungguard.pkl")
    }

    # Verify model files exist
    for name, path in model_paths.items():
        if not os.path.exists(path):
            print(json.dumps({
                "error": f"Model file not found: {path}",
                "fallback_needed": True
            }))
            sys.exit(1)

    try:
        # Load all models using joblib
        model_hcn_air = joblib.load(model_paths["hcn_air"])
        model_hcn_umbi = joblib.load(model_paths["hcn_umbi"])
        model_status_air = joblib.load(model_paths["status_air"])
        model_status_gadung = joblib.load(model_paths["status_gadung"])

        # Prepare input features
        features = np.array([[ph, turbidity, tds, temp]])

        # Predict
        hcn_air_pred = float(model_hcn_air.predict(features)[0])
        hcn_umbi_pred = float(model_hcn_umbi.predict(features)[0])
        status_air_pred = str(model_status_air.predict(features)[0])
        status_gadung_pred = str(model_status_gadung.predict(features)[0])

        # safety_status: worst of status_air and status_gadung
        # Severity mapping: Bahaya > Proses > Aman
        severity = {"Bahaya": 3, "Proses": 2, "Aman": 1}
        status_air_severity = severity.get(status_air_pred, 1)
        status_gadung_severity = severity.get(status_gadung_pred, 1)

        if max(status_air_severity, status_gadung_severity) == 3:
            safety_status = "Bahaya"
        elif max(status_air_severity, status_gadung_severity) == 2:
            safety_status = "Proses"
        else:
            safety_status = "Aman"

        # Output JSON response
        output = {
            "hcn_air": hcn_air_pred,
            "hcn_umbi": hcn_umbi_pred,
            "status_air": status_air_pred,
            "status_gadung": status_gadung_pred,
            "safety_status": safety_status,
            "fallback_needed": False
        }
        print(json.dumps(output))

    except Exception as e:
        print(json.dumps({
            "error": f"Prediction failed: {str(e)}",
            "fallback_needed": True
        }))
        sys.exit(1)

if __name__ == "__main__":
    main()
